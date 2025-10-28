<?php

namespace Beeralex\Notification\Channels;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\Dto\NotificationMessage;
use Beeralex\Notification\Enum\Channel;
use Beeralex\Notification\Events\SmsEvent;

class SmsChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        return (new SmsEvent($message->eventName, $message->fields))->send();
    }

    public static function getDisplayName(): string
    {
        return 'SMS уведомления';
    }

    public static function getCode(): string
    {
        return Channel::SMS->value;
    }
}
