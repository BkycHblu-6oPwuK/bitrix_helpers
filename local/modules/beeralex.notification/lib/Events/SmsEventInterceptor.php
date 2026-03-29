<?php

namespace Beeralex\Notification\Events;

use Beeralex\Notification\NotificationManager;
use Beeralex\Notification\NotificationLock;
use Beeralex\Notification\Dto\NotificationMessage;
use Beeralex\Notification\Enum\Channel;
use Beeralex\User\Contracts\UserRepositoryContract;

use function Beeralex\Notification\log;

class SmsEventInterceptor
{
    public function __construct(
        public bool $moduleEnable = false
    ) {}

    /**
     * поля переданные в событие SMS
     */
    public function handle(string $eventName, array $fields): \Bitrix\Main\Result
    {
        $result = new \Bitrix\Main\Result;
        try {
            if (!$this->moduleEnable || !$eventName || NotificationLock::isLocked(Channel::SMS->value) || NotificationLock::isLocked(Channel::EMAIL->value)) {
                return $result;
            }

            $userId = \service(UserRepositoryContract::class)->getCurrentUser()->getId();
            if (!$userId) {
                return $result;
            }

            NotificationLock::lock(Channel::SMS->value);
            try {
                $notification = new NotificationMessage($eventName, $fields, $userId);
                (new NotificationManager())->notify($notification);
            } finally {
                NotificationLock::unlock(Channel::SMS->value);
            }

            return $result->addError(new \Bitrix\Main\Error('Перехвачено отправление SMS через стандартный обработчик'));;
        } catch (\Exception $e) {
            log('Ошибка в SmsEventInterceptor: ' . $e->getMessage(), 6, true);
        }
        return $result;
    }
}
