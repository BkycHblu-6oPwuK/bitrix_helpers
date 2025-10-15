<?php
namespace App\User\Auth\Authenticators;

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
}
