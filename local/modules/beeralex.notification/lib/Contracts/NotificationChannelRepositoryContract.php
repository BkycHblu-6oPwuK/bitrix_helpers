<?

namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

/**
 * Репозиторий для работы с каналами уведомлений (email, sms, telegram)
 */
interface NotificationChannelRepositoryContract extends RepositoryContract
{
    /**
     * Получить активные каналы
     */
    public function getActiveChannels(): array;

    /**
     * Получить канал по коду (например, 'email', 'sms', 'telegram')
     */
    public function getByCode(string $code): ?array;

    /**
     * Активировать канал
     */
    public function activate(int $id): void;

    /**
     * Деактивировать канал
     */
    public function deactivate(int $id): void;

    public function getNonEmailChannels(): array;
}
