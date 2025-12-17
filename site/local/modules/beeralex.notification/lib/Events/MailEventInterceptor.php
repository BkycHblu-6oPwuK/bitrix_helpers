<?php

namespace Beeralex\Notification\Events;

use Beeralex\Notification\Dto\NotificationMessage;
use Beeralex\Notification\NotificationManager;
use Beeralex\Notification\NotificationLock;
use Beeralex\Notification\Enum\Channel;
use Beeralex\User\Contracts\UserRepositoryContract;

use function Beeralex\Notification\log;

class MailEventInterceptor
{
    public function __construct(
        public bool $moduleEnable = false
    ){}

    public function handle(
        string $eventName,
        string $lid,
        array $fields,
        ?string $messageId = null,
        ?array $files = null,
        ?string $languageId = null
    ): bool {
        try {
            if (!$this->moduleEnable || !$eventName || NotificationLock::isLocked(Channel::EMAIL->value) || NotificationLock::isLocked(Channel::SMS->value)) {
                return true;
            }

            $userId = \service(UserRepositoryContract::class)->getCurrentUser()->getId();
            if (!$userId) {
                return true;
            }

            NotificationLock::lock(Channel::EMAIL->value);
            try {
                $message = new NotificationMessage($eventName, $fields, $userId, $lid, $messageId, $files, $languageId);
                $manager = new NotificationManager();
                $manager->notify($message);
            } finally {
                NotificationLock::unlock(Channel::EMAIL->value);
            }

            return false;
        } catch (\Exception $e) {
            log('Ошибка перехвата почтового события ' . $eventName . ': ' . $e->getMessage(), 6, true);
        }
        return true;
    }
}
