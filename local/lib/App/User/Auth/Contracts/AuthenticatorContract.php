<?php

namespace App\User\Auth\Contracts;

use App\User\Dto\BaseUserDto;
use App\User\User;

interface AuthenticatorContract
{
    /**
     * Уникальный ключ (идентификатор) аутентификатора.
     */
    public static function getKey(): string;

    /**
     * Аутентифицировать пользователя.
     */
    public function authenticate(BaseUserDto $data): void;

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
}
