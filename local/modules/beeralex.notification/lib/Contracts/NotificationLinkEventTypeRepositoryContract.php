<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface NotificationLinkEventTypeRepositoryContract extends RepositoryContract
{
    /**
     * Получить все связи для указанного типа уведомления
     */
    public function getByNotificationType(int $notificationTypeId): array;

    /**
     * Получить все связи для конкретного события Bitrix
     */
    public function getByEventName(string $eventName): array;

    /**
     * Получить одну связь по типу уведомления и событию
     */
    public function getByPair(int $notificationTypeId, string $eventName): ?array;

    /**
     * Удалить все связи по типу уведомления
     */
    public function deleteByNotificationType(int $notificationTypeId): void;

    /**
     * Проверить наличие связи между типом уведомления и событием
     */
    public function hasLink(int $notificationTypeId, int $eventId): bool;
}