<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Contracts;

use Beeralex\User\Dto\AuthCredentialsDto;
use Beeralex\User\User;
use Bitrix\Main\Result;

interface AuthenticatorContract
{
    /**
     * Уникальный ключ (идентификатор) аутентификатора.
     */
    public function getKey(): string;

    /**
     * Аутентифицировать пользователя.
     */
    public function authenticate(?AuthCredentialsDto $data = null): Result;

    /**
     * Зарегистрировать нового пользователя.
     */
    public function register(AuthCredentialsDto $user): Result;

    /**
     * Человекочитаемое название аутентификатора.
     */
    public function getTitle(): string;

    /**
     * Краткое описание аутентификатора.
     */
    public function getDescription(): ?string;

    /**
     * URL логотипа (или null, если отсутствует).
     */
    public function getLogoUrl(): ?string;

    /**
     * Является ли аутентификатором внешний сервис.
     */
    public function isService(): bool;

    /**
     * Получить URL для перенаправления на страницу авторизации внешнего сервиса или html для вставки
     * @return array{type:string,value:string}
     */
    public function getAuthorizationUrlOrHtml(): ?array;
}
