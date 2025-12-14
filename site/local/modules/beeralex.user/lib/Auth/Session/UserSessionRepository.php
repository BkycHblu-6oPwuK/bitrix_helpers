<?php
declare(strict_types=1);

namespace Beeralex\User\Auth\Session;

use Beeralex\Core\Repository\Repository;
use Bitrix\Main\Type\DateTime;

class UserSessionRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(UserSessionTable::class);
    }

    /**
     * Получить список активных устройств пользователя
     */
    public function getActiveSessions(int $userId): array
    {
        return $this->all([
            'USER_ID' => $userId,
            'REVOKED' => 'N',
        ], order: ['LAST_ACTIVITY' => 'DESC']);
    }

    /**
     * Получить количество активных сессий
     */
    public function countActiveSessions(int $userId): int
    {
        return $this->count([
            'USER_ID' => $userId,
            'REVOKED' => 'N',
        ]);
    }

    /**
     * Найти сессию по ID
     */
    public function findById(int $id): ?array
    {
        return $this->one(['ID' => $id]);
    }

    /**
     * Создать новую сессию
     */
    public function createSession(
        int $userId,
        string $refreshToken,
        string $userAgent,
        string $ip
    ): int {
        return $this->add([
            'USER_ID' => $userId,
            'REFRESH_TOKEN' => $refreshToken,
            'USER_AGENT' => $userAgent,
            'IP_ADDRESS' => $ip,
            'REVOKED' => 'N',
        ]);
    }

    /**
     * Обновить активность (например при refresh)
     */
    public function touchSession(int $id): void
    {
        $this->update($id, [
            'LAST_ACTIVITY' => new DateTime(),
        ]);
    }

    /**
     * Обновить device info если поменялось (браузер, ip)
     */
    public function updateDeviceInfo(int $id, string $userAgent, string $ip): void
    {
        $this->update($id, [
            'USER_AGENT' => $userAgent,
            'IP_ADDRESS'  => $ip,
        ]);
    }

    /**
     * Завершить одну сессию (например при Logout)
     */
    public function revokeSession(int $id): void
    {
        $this->update($id, ['REVOKED' => 'Y']);
    }

    /**
     * Завершить все сессии пользователя
     */
    public function revokeAll(int $userId): void
    {
        $sessions = $this->getActiveSessions($userId);
        foreach ($sessions as $session) {
            $this->revokeSession((int)$session['ID']);
        }
    }

    /**
     * Завершить все кроме текущей
     */
    public function revokeAllExcept(int $userId, string $currentRefreshToken): void
    {
        $sessions = $this->getActiveSessions($userId);

        foreach ($sessions as $session) {
            if ($session['REFRESH_TOKEN'] !== $currentRefreshToken) {
                $this->revokeSession((int)$session['ID']);
            }
        }
    }

    /**
     * Найти сессию по refresh токену
     */
    public function findByToken(string $refreshToken): ?array
    {
        return $this->one([
            'REFRESH_TOKEN' => $refreshToken,
            'REVOKED' => 'N',
        ]);
    }

    /**
     * Проверить, принадлежит ли refresh token пользователю
     */
    public function isTokenOwnedByUser(int $userId, string $refreshToken): bool
    {
        return (bool)$this->one([
            'USER_ID' => $userId,
            'REFRESH_TOKEN' => $refreshToken,
            'REVOKED' => 'N',
        ]);
    }

    /**
     * Удалить сессию по токену
     */
    public function revokeByToken(string $refreshToken): void
    {
        $session = $this->findByToken($refreshToken);
        if ($session) {
            $this->revokeSession((int)$session['ID']);
        }
    }

    /**
     * Удалить старые сессии, например более 90 дней
     */
    public function cleanupOldSessions(int $days = 90): void
    {
        $threshold = (new DateTime())->add("-{$days} days");

        $oldSessions = $this->all([
            '<LAST_ACTIVITY' => $threshold,
        ]);

        foreach ($oldSessions as $session) {
            $this->revokeSession((int)$session['ID']);
        }
    }

    /**
     * Обновить refresh токен в текущей сессии (длинная сессия)
     */
    public function replaceRefreshToken(int $id, string $newRefreshToken): void
    {
        $this->update($id, [
            'REFRESH_TOKEN' => $newRefreshToken,
            'LAST_ACTIVITY' => new DateTime(),
        ]);
    }

    public function findSessionsByUser(int $userId): array
    {
        return $this->all([
            'USER_ID' => $userId,
        ]);
    }
}
