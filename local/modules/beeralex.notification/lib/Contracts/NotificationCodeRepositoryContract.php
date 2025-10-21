<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface NotificationCodeRepositoryContract extends RepositoryContract
{
    /**
     * Получить активный (неиспользованный и неистекший) код
     */
    public function getValidCode(string $recipient, string $purpose, string $channel): ?array;

    /**
     * Проверить существование действующего кода
     */
    public function hasValidCode(string $recipient, string $purpose, string $channel): bool;

    /**
     * Отметить код как использованный
     */
    public function markAsUsed(int $id): void;

    /**
     * Очистить просроченные коды
     */
    public function deleteExpiredCodes(): void;

    /**
     * Получить последние коды пользователя
     */
    public function getUserCodes(int $userId, int $limit = 10): array;
}