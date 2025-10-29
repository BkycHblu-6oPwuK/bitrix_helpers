<?php

namespace Beeralex\User\Auth\Contracts;

use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\User;

interface AuthenticatorContract
{
    /**
     * Уникальный ключ (идентификатор) аутентификатора.
     */
    public function getKey(): string;

    /**
     * Аутентифицировать пользователя.
     */
    public function authenticate(?BaseUserDto $data = null): void;

    /**
     * Зарегистрировать пользователя (для локальных сценариев).
     */
    public function register(BaseUserDto $user): void;

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
