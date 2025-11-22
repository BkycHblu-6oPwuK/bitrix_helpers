<?php
declare(strict_types=1);
namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Contracts\UserPhoneAuthRepositoryContract;
use Beeralex\User\Phone;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
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
     */
    protected function generateCode(int $userId): Result
    {
        $result = new Result();
        $row = $this->repository->getByUserId($userId);

        if (!$row || empty($row['OTP_SECRET'])) {
            $result->addError(new Error('Не найден секрет OTP для пользователя.', 'user'));
            return $result;
        }

        $totp = $this->getTotp($row['OTP_SECRET']);
        $code = $totp->generateOTP($totp->timecode(time()));

        $this->repository->update($userId, [
            'ATTEMPTS' => 0,
            'DATE_SENT' => new DateTime(),
        ]);

        $result->setData([
            'code' => $code,
            'phone' => $row['PHONE_NUMBER'],
        ]);
        return $result;
    }

    /**
     * Отправка SMS с кодом пользователю
     *
     */
    public function sendCode(Phone $phone, string $template = 'SMS_USER_CONFIRM_NUMBER', ?string $siteId = null): Result
    {
        $result = new Result();
        $row = $this->repository->getByPhone($phone);

        if (!$row) {
            $result->addError(new Error('Пользователь с таким номером не найден.', 'user'));
            return $result;
        }

        if (!empty($row['DATE_SENT'])) {
            $now = new DateTime();
            $diff = $now->getTimestamp() - $row['DATE_SENT']->getTimestamp();
            if ($diff < self::RESEND_INTERVAL) {
                $result->addError(new Error('Код уже был отправлен недавно. Попробуйте позже.', 'user'));
                return $result;
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
            $result->addErrors($sendResult->getErrors());
            return $result;
        }
        return $result;
    }

    /**
     * Проверяет правильность кода.
     *
     * @return int Идентификатор пользователя
     */
    public function verifyCode(Phone $phone, string $code): Result
    {
        $result = new Result();
        $row = $this->repository->getByPhone($phone);

        if (!$row || empty($row['OTP_SECRET'])) {
            $result->addError(new Error('Пользователь не найден или не имеет OTP секрета.', 'user'));
            return $result;
        }

        if ((int)$row['ATTEMPTS'] >= self::MAX_ATTEMPTS) {
            $result->addError(new Error('Превышено количество попыток. Попробуйте позже.', 'user'));
            return $result;
        }

        $totp = $this->getTotp($row['OTP_SECRET']);

        try {
            [$isValid] = $totp->verify($code);
        } catch (\Throwable $e) {
            $result->addError(new Error('Ошибка при проверке кода: ' . $e->getMessage(), 'verification'));
            return $result;
        }

        if ($isValid) {
            $this->repository->markConfirmed((int)$row['USER_ID']);
            $result->setData(['userId' => (int)$row['USER_ID']]);
            return $result;
        }

        $this->repository->incrementAttempts((int)$row['USER_ID']);
        $result->addError(new Error('Неверный код подтверждения.', 'verification'));
        return $result;
    }
}
