<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Dto\TelegramAuthRequestDto;
use App\User\Dto\BaseUserDto;
use Beeralex\Core\Config\Config;
use Bitrix\Main\ArgumentException;

class TelegramAuthenticator extends BaseServiceAuthentificator
{
    public static function getKey(): string
    {
        return 'telegram';
    }

    public function getTitle(): string
    {
        return 'Telegram';
    }

    public function getDescription(): ?string
    {
        return 'Авторизация через Telegram Login Widget';
    }

    public function getLogoUrl(): ?string
    {
        return '/images/auth/telegram.svg';
    }

    public function authenticate(BaseUserDto $data): void
    {
        if (!($data instanceof TelegramAuthRequestDto)) {
            throw new ArgumentException('expected subclass TelegramAuthRequestDto');
        }

        if(!$this->verifyTelegramAuthData($data)) {
            throw new ArgumentException('not valid data');
        }

        parent::authenticate($data);
    }

    /**
     * Проверяет подпись, полученную от Telegram
     *
     * @param array $authData — данные, полученные из Telegram Login Widget
     */
    protected function verifyTelegramAuthData(TelegramAuthRequestDto $data): bool
    {
        $botToken = Config::getInstance()['TELEGRAM_BOT_TOKEN'];
       
        if (empty($data->hash) || !$botToken) {
            return false;
        }
        $authData = $data->getDataForValidation();
        
        ksort($authData);
        $dataCheckString = implode("\n", array_map(
            fn($k, $v) => "$k=$v",
            array_keys($authData),
            $authData
        ));

        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $data->hash);
    }
}
