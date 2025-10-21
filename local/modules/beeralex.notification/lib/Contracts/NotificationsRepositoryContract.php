<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface NotificationsRepositoryContract extends RepositoryContract
{
    /**
     * Получить все уведомления по каналу
     */
    public function getByChannel(string $channel): array;

    /**
     * Получить уведомления по статусу
     */
    public function getByStatus(string $status): array;

    /**
     * Получить уведомления по получателю
     */
    public function getByRecipient(string $recipient): array;

    /**
     * Получить уведомления по ID кода
     */
    public function getByCodeId(int $codeId): array;

    /**
     * Обновить статус уведомления
     */
    public function updateStatus(int $id, string $status): void;
    /**
     * Получить новые уведомления
     */
    public function getNew(): array;

    /**
     * Получить все уведомления, созданные за последние N часов
     */
    public function getRecent(int $hours = 24): array;
}