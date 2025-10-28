<?php

namespace Beeralex\Notification\Channels;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\DTO\NotificationMessage;
use Beeralex\Notification\Enum\Channel;

class TelegramChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        return new \Bitrix\Main\Result();
    }

    public static function getDisplayName(): string
    {
        return 'Telegram уведомления';
    }

    public static function getCode(): string
    {
        return Channel::TELEGRAM->value;
    }
}
