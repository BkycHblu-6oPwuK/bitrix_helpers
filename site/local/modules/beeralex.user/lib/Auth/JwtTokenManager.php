<?php
declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\User\Options;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Менеджер для работы с JWT токенами
 * Использует Firebase JWT библиотеку
 */
class JwtTokenManager
{
    /**
     * Простейший in-memory blacklist для refresh токенов (по jti или hash).
     * В проде имеет смысл заменить на persistent storage (DB/Redis).
     * Ключ: string (jti либо sha256(token)), значение: true.
     * Внимание: живёт в рамках процесса PHP (CLI/FPM воркера).
     *
     * @var array<string,bool>
     */
    private static array $revokedRefreshIndex = [];

    public function __construct(
        protected readonly Options $options
    ) {}

    /**
     * Генерация access токена
     *
     * @param int $userId ID пользователя
     * @param array $additionalClaims Дополнительные данные для токена
     * @return string JWT токен
     * @throws \InvalidArgumentException
     */
    public function generateAccessToken(int $userId, array $additionalClaims = []): string
    {
        if (!$this->options->enableJwtAuth) {
            throw new \RuntimeException('JWT authentication is disabled');
        }

        if (empty($this->options->jwtSecretKey)) {
            throw new \InvalidArgumentException('JWT secret key is not configured');
        }

        $now = time();
        $payload = [
            'iss' => $this->options->jwtIssuer,      // Издатель
            'iat' => $now,                            // Время создания
            'exp' => $now + $this->options->jwtTtl,  // Время истечения
            'sub' => (string)$userId,                 // Субъект (ID пользователя)
            'type' => 'access',                       // Тип токена
            'jti' => $this->generateJti(),            // Идентификатор токена
        ];

        // Добавляем дополнительные claims
        $payload = array_merge($payload, $additionalClaims);

        return JWT::encode($payload, $this->options->jwtSecretKey, $this->options->jwtAlgorithm);
    }

    /**
     * Генерация refresh токена
     *
     * @param int $userId ID пользователя
     * @return string JWT refresh токен
     * @throws \InvalidArgumentException
     */
    public function generateRefreshToken(int $userId): string
    {
        if (!$this->options->enableJwtAuth) {
            throw new \RuntimeException('JWT authentication is disabled');
        }

        if (empty($this->options->jwtSecretKey)) {
            throw new \InvalidArgumentException('JWT secret key is not configured');
        }

        $now = time();
        $payload = [
            'iss' => $this->options->jwtIssuer,
            'iat' => $now,
            'exp' => $now + $this->options->jwtRefreshTtl,
            'sub' => (string)$userId,
            'type' => 'refresh',
            'jti' => $this->generateJti(),
        ];

        return JWT::encode($payload, $this->options->jwtSecretKey, $this->options->jwtAlgorithm);
    }

    /**
     * Генерация пары токенов (access + refresh)
     *
     * @param int $userId ID пользователя
     * @param array $additionalClaims Дополнительные данные для access токена
     * @return array{access: string, refresh: string}
     */
    public function generateTokenPair(int $userId, array $additionalClaims = []): array
    {
        return [
            'access' => $this->generateAccessToken($userId, $additionalClaims),
            'refresh' => $this->generateRefreshToken($userId),
        ];
    }

    /**
     * Валидация и декодирование токена
     *
     * @param string $token JWT токен
     * @return object Декодированные данные токена
     * @throws ExpiredException Токен истёк
     * @throws SignatureInvalidException Неверная подпись
     * @throws \InvalidArgumentException Невалидный токен
     */
    public function verifyToken(string $token): object
    {
        if (!$this->options->enableJwtAuth) {
            throw new \RuntimeException('JWT authentication is disabled');
        }

        if (empty($this->options->jwtSecretKey)) {
            throw new \InvalidArgumentException('JWT secret key is not configured');
        }

        try {
            return JWT::decode($token, new Key($this->options->jwtSecretKey, $this->options->jwtAlgorithm));
        } catch (ExpiredException $e) {
            throw new ExpiredException('Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new SignatureInvalidException('Invalid token signature');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Получить ID пользователя из токена
     *
     * @param string $token JWT токен
     * @return int ID пользователя
     */
    public function getUserIdFromToken(string $token): int
    {
        $decoded = $this->verifyToken($token);
        return (int)$decoded->sub;
    }

    /**
     * Проверка, является ли токен access токеном
     *
     * @param string $token JWT токен
     * @return bool
     */
    public function isAccessToken(string $token): bool
    {
        try {
            $decoded = $this->verifyToken($token);
            return isset($decoded->type) && $decoded->type === 'access';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверка, является ли токен refresh токеном
     *
     * @param string $token JWT токен
     * @return bool
     */
    public function isRefreshToken(string $token): bool
    {
        try {
            $decoded = $this->verifyToken($token);
            return isset($decoded->type) && $decoded->type === 'refresh';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверка, отозван ли refresh-токен (по jti/хэшу).
     */
    public function isRefreshRevoked(string $refreshToken): bool
    {
        try {
            $decoded = $this->verifyToken($refreshToken);
        } catch (\Throwable) {
            // Невалидный/истёкший — с точки зрения ревокации считать как "неактивен"
            return true;
        }
        $key = $this->makeRevocationKey($decoded, $refreshToken);
        return isset(static::$revokedRefreshIndex[$key]);
    }

    /**
     * Обновление токенов по refresh токену
     *
     * @param string $refreshToken Refresh токен
     * @param array $additionalClaims Дополнительные данные для нового access токена
     * @return array{access: string, refresh: string}
     * @throws \InvalidArgumentException
     */
    public function refreshTokens(string $refreshToken, array $additionalClaims = []): array
    {
        if(!$this->isEnabled()) {
            throw new \RuntimeException('JWT authentication is disabled');
        }

        if (!$this->isRefreshToken($refreshToken)) {
            throw new \InvalidArgumentException('Invalid refresh token');
        }

        if ($this->isRefreshRevoked($refreshToken)) {
            throw new \InvalidArgumentException('Refresh token has been revoked');
        }

        $decoded = $this->verifyToken($refreshToken);
        $userId = (int)$decoded->sub;

        // По желанию можно реализовать rotate: при успешном рефреше отзывать старый refresh.
        // $this->revokeRefreshToken($refreshToken);

        return $this->generateTokenPair($userId, $additionalClaims);
    }

    /**
     * Отзыв (ревокация) refresh токена. Нужен для logout или ручного отзыва.
     * По умолчанию — добавляет запись в in-memory blacklist.
     * Для production лучше заменить на persistent storage (DB/Redis) и TTL.
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        try {
            $decoded = $this->verifyToken($refreshToken);
        } catch (\Throwable) {
            // уже невалиден — можно считать отозванным
            return;
        }
        $key = $this->makeRevocationKey($decoded, $refreshToken);
        static::$revokedRefreshIndex[$key] = true;
    }

    /**
     * Извлечение токена из заголовка Authorization
     *
     * @param string $authorizationHeader Значение заголовка Authorization
     * @return string|null JWT токен или null
     */
    public function extractTokenFromHeader(string $authorizationHeader): ?string
    {
        // Формат: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Получить claims в виде массива (helper).
     *
     * @return array<string,mixed>
     */
    public function getTokenClaims(string $token): array
    {
        $decoded = $this->verifyToken($token);
        return json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Проверка, включена ли JWT авторизация
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->options->enableJwtAuth && !empty($this->options->jwtSecretKey);
    }

    // -------------------- Внутренние помощники --------------------

    private function generateJti(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Ключ для black/allow-листа: по возможности jti, иначе sha256 от всего токена.
     *
     * @param object $decoded
     */
    private function makeRevocationKey(object $decoded, string $rawToken): string
    {
        /** @var string|null $jti */
        $jti = isset($decoded->jti) && is_string($decoded->jti) ? $decoded->jti : null;
        return $jti ?: hash('sha256', $rawToken);
    }
}