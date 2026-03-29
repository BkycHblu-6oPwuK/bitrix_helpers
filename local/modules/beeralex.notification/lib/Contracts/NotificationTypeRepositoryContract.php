<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface NotificationTypeRepositoryContract extends RepositoryContract
{
    /**
     * Получить тип уведомления по коду
     */
    public function getByCode(string $code): ?array;
    /**
     * Получить все типы уведомлений
     */
    public function getAllTypes(): array;

    /**
     * Проверить, существует ли тип уведомления с указанным кодом
     */
    public function exists(string $code): bool;

    /**
     * Добавить новый тип уведомления, если такого ещё нет
     */
    public function addIfNotExists(string $code, string $name): int;
}