<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface UserNotificationPreferenceRepositoryContract extends RepositoryContract
{
    /**
     * Получить все предпочтения пользователя
     */
    public function getByUser(int $userId): array;

    /**
     * Получить предпочтение пользователя по типу уведомления и каналу
     */
    public function getUserPreference(int $userId, int $notificationTypeId, int $channelId): ?array;

    /**
     * Проверить, включено ли уведомление по каналу
     */
    public function isEnabled(int $userId, int $notificationTypeId, int $channelId): bool;
    /**
     * Установить состояние уведомления (включить/выключить)
     */
    public function setEnabled(int $userId, int $notificationTypeId, int $channelId, bool $enabled): void;

    /**
     * Обновление записи с составным первичным ключом
     */
    public function updateCompositeKey(array $data): void;
}