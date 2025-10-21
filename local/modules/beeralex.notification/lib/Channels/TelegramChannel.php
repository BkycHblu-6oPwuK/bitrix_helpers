<?php

namespace Beeralex\Notification\Channels;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\DTO\NotificationMessage;

class TelegramChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): bool
    {
        return true;
    }
}
