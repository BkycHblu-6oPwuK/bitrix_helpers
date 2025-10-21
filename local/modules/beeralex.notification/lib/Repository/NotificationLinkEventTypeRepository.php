<?php
namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationLinkEventTypeRepositoryContract;
use Beeralex\Notification\Tables\NotificationLinkEventTypeTable;

class NotificationLinkEventTypeRepository extends Repository implements NotificationLinkEventTypeRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationLinkEventTypeTable::class);
    }

    public function getByNotificationType(int $notificationTypeId): array
    {
        return $this->all([
            '=EVENT_TYPE_ID' => $notificationTypeId,
        ]);
    }

    public function getByEventId(int $eventId): array
    {
        return $this->all([
            '=EVENT_ID' => $eventId,
        ]);
    }

    public function getByPair(int $notificationTypeId, int $eventId): ?array
    {
        return $this->one([
            '=EVENT_TYPE_ID' => $notificationTypeId,
            '=EVENT_ID' => $eventId,
        ]);
    }

    public function deleteByNotificationType(int $notificationTypeId): void
    {
        $links = $this->getByNotificationType($notificationTypeId);
        foreach ($links as $link) {
            $this->delete($link['ID']);
        }
    }

    public function hasLink(int $notificationTypeId, int $eventId): bool
    {
        return (bool)$this->getByPair($notificationTypeId, $eventId);
    }
}
