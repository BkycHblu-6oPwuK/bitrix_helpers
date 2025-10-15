<?php

namespace App\User\Auth\Contracts;

interface ExternalAuthRepositoryContract
{
    /**
     * Получить связь по пользователю и сервису
     */
    public function getByUserAndService(int $userId, string $service): ?array;

    /**
     * Получить связь по внешнему идентификатору и сервису
     */
    public function getByExternalId(string $externalId, string $service): ?array;

    /**
     * Создать или обновить запись о связи пользователя и внешнего аккаунта
     */
    public function saveLink(int $userId, string $service, string $externalId, ?string $accessToken = null, ?string $refreshToken = null): int;

    /**
     * Удалить связь пользователя с сервисом
     */
    public function deleteByUserAndService(int $userId, string $service): void;
}
