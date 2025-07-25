<?php

namespace Itb\Services\Sms;

use Itb\Contracts\SmsContract;
use Itb\Core\Config;
use Itb\Exceptions\SmsException;
use Itb\User\Phone\Phone;

class SmsAeroService implements SmsContract
{
    protected readonly string $smsAeroEmail;
    protected readonly string $smsAeroApi;
    protected readonly bool $isEnable;
    protected string $sign = 'SMS Aero';

    public function __construct()
    {
        $config = Config::getInstance();
        $this->smsAeroEmail = $config->smsAeroEmail;
        $this->smsAeroApi = $config->smsAeroApi;
        $this->isEnable = $config->isEnableSendSms;
    }

    /**
     * @throws SmsException
     * @throws \Exception
     */
    public function sendSms(Phone $phone, string $message) : array
    {
        if (!$this->isEnable) return [];
        $number = $phone->getNumber();
        if (strlen($number) < 11) {
            throw new \RuntimeException('Неверно введен номер телефона');
        }
        $smsAeroMessage = new \SmsAero\SmsAeroMessage($this->smsAeroEmail, $this->smsAeroApi);
        $response = $smsAeroMessage->send(['number' => $number, 'text' => $message, 'sign' => $this->sign]);
        if (!$response['success']) {
            throw new SmsException($response['message'] ?? 'Ошибка при отправке смс');
        }
        $response = [];
        return $response;
    }

    public function setSign(string $sign): static
    {
        $this->sign = $sign;
        return $this;
    }
}
