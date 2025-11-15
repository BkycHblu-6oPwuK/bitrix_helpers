<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Contracts;

use Beeralex\User\Auth\Social\Contracts\AuthUserInterface;

/**
 * Интерфейс, определяющий поведение любого внешнего сервиса авторизации (Telegram, VK, Google и т.д.)
 */
interface AuthServiceContract
{
    /**
     * Проверяет подлинность данных, полученных от внешнего сервиса.
     *
     * @param array $data Данные от внешнего API (например, $_GET или JSON payload)
     * @return bool true — если данные прошли проверку подлинности, иначе false.
     */
    public function verify(array $data): bool;

    /**
     * Преобразует "сырой" ответ внешнего API в унифицированный объект пользователя.
     *
     * @param array $data Данные о пользователе от внешнего сервиса
     * @return AuthUserInterface Объект пользователя в формате, понятном системе.
     */
    public function getUser(array $data): AuthUserInterface;
}
