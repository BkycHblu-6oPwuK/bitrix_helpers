<?php

namespace Beeralex\Notification\Channels;

use Bitrix\Main\Sms\Event as SmsEvent;
use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\Dto\NotificationMessage;

class SmsChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): bool
    {
        $result = (new SmsEvent($message->eventName, $message->fields))->send();

        return $result->isSuccess();
    }
}
