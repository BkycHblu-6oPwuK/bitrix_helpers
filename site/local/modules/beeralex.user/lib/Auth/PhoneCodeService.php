<?php
declare(strict_types=1);
namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Contracts\UserPhoneAuthRepositoryContract;
use Beeralex\User\Phone;
use Beeralex\User\Exceptions\PhoneCodeException;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Mfa\TotpAlgorithm;
use Bitrix\Main\Type\DateTime;

class PhoneCodeService
{
    protected const OTP_INTERVAL = 300;
    protected const RESEND_INTERVAL = 60;
    protected const MAX_ATTEMPTS = 3;

    public function __construct(
        protected readonly UserPhoneAuthRepositoryContract $repository
    ) {}

    protected function getTotp(string $secret): TotpAlgorithm
    {
        return (new TotpAlgorithm())
            ->setInterval(self::OTP_INTERVAL)
            ->setSecret($secret);
    }

    /**
     * Генерирует одноразовый код и сохраняет дату отправки
     *
     * @return array{code: string, phone: string}
     * @throws PhoneCodeException
     */
    protected function generateCode(int $userId): array
    {
        $row = $this->repository->getByUserId($userId);

        if (!$row || empty($row['OTP_SECRET'])) {
            throw new PhoneCodeException('Не найден секрет OTP для пользователя.');
        }

        $totp = $this->getTotp($row['OTP_SECRET']);
        $code = $totp->generateOTP($totp->timecode(time()));

        $this->repository->update($userId, [
            'ATTEMPTS' => 0,
            'DATE_SENT' => new DateTime(),
        ]);

        return [
            'code' => $code,
            'phone' => $row['PHONE_NUMBER'],
        ];
    }

    /**
     * Отправка SMS с кодом пользователю
     *
     * @throws PhoneCodeException
     */
    public function sendCode(Phone $phone, string $template = 'SMS_USER_CONFIRM_NUMBER', ?string $siteId = null): true
    {
        $row = $this->repository->getByPhone($phone);

        if (!$row) {
            throw new PhoneCodeException('Пользователь с таким номером не найден.');
        }

        if (!empty($row['DATE_SENT'])) {
            $now = new DateTime();
            $diff = $now->getTimestamp() - $row['DATE_SENT']->getTimestamp();
            if ($diff < self::RESEND_INTERVAL) {
                throw new PhoneCodeException('Код уже был отправлен недавно. Попробуйте позже.');
            }
        }

        $data = $this->generateCode((int)$row['USER_ID']);

        if (Loader::includeModule('beeralex.notification')) {
            $sms = new \Beeralex\Notification\Events\SmsEvent($template, [
                'USER_PHONE' => $data['phone'],
                'CODE' => $data['code'],
            ]);
        } else {
            $sms = new \Bitrix\Main\Sms\Event($template, [
                'USER_PHONE' => $data['phone'],
                'CODE' => $data['code'],
            ]);
        }


        $sms->setSite($siteId);

        $sendResult = $sms->send(true);
        if (!$sendResult->isSuccess()) {
            $messages = implode('; ', $sendResult->getErrorMessages());
            throw new PhoneCodeException("Ошибка отправки SMS: {$messages}");
        }
        return true;
    }

    /**
     * Проверяет правильность кода.
     *
     * @return int Идентификатор пользователя
     * @throws PhoneCodeException
     */
    public function verifyCode(Phone $phone, string $code): int
    {
        $row = $this->repository->getByPhone($phone);

        if (!$row || empty($row['OTP_SECRET'])) {
            throw new PhoneCodeException('Пользователь не найден или не имеет OTP секрета.');
        }

        if ((int)$row['ATTEMPTS'] >= self::MAX_ATTEMPTS) {
            throw new PhoneCodeException('Превышено количество попыток. Попробуйте позже.');
        }

        $totp = $this->getTotp($row['OTP_SECRET']);

        try {
            [$isValid] = $totp->verify($code);
        } catch (\Throwable $e) {
            throw new PhoneCodeException('Ошибка при проверке кода: ' . $e->getMessage(), previous: $e);
        }

        if ($isValid) {
            $this->repository->markConfirmed((int)$row['USER_ID']);
            return (int)$row['USER_ID'];
        }

        $this->repository->incrementAttempts((int)$row['USER_ID']);
        throw new PhoneCodeException('Неверный код подтверждения.');
    }
}
