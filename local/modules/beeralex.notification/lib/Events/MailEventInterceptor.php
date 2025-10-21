<?php

namespace Beeralex\Notification\Events;

use Beeralex\Notification\DTO\NotificationMessage;
use Beeralex\Notification\NotificationManager;
use App\User\UserRepository;

class MailEventInterceptor
{
    public function handle(array $fields, array $template): bool
    {
        try {
            $eventName = $template['EVENT_NAME'] ?? null;
            if (!$eventName) {
                return true;
            }

            $userId = (int)($fields['USER_ID'] ?? 0);
            if (!$userId && !empty($fields['EMAIL'])) {
                $user = (new UserRepository())->getByEmail($fields['EMAIL']);
                $userId = $user?->getId() ?? 0;
            }

            if (!$userId) {
                return true;
            }

            $message = new NotificationMessage($eventName, $fields, $userId);
            $manager = new NotificationManager();
            $manager->notify($message);

            return false;
        } catch (\Exception $e) {
            \AddMessage2Log('Ошибка в MailEventInterceptor: ' . $e->getMessage());
            return true;
        }
    }
}
