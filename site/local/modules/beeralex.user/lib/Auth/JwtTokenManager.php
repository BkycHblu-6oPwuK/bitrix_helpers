<?php

declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\Core\Dto\CacheSettingsDTO;
use Beeralex\Core\Traits\Cacheable;
use Beeralex\User\Auth\Session\UserSessionRepository;
use Beeralex\User\Options;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;
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
    use Cacheable;

    public function __construct(
        protected readonly Options $options,
        protected readonly UserSessionRepository $sessionRepository
    ) {}

    protected function makeCacheSettingsDTO(string $key): CacheSettingsDTO
    {
        return new CacheSettingsDTO(
            time: 3600,
            key: $key,
            dir: 'beeralex/user/jwt',
        );
    }

    /**
     * Генерация access токена
     *
     * @param int $userId ID пользователя
     * @param array $additionalClaims Дополнительные данные для токена
     * @return Result<array<string, string>> JWT токен
     * @throws \InvalidArgumentException
     */
    public function generateAccessToken(int $userId, array $additionalClaims = []): Result
    {
        $result = new Result();
        if (!$this->options->enableJwtAuth) {
            return $result->addError(new \Bitrix\Main\Error('JWT disabled'));
        }

        $now = time();
        $payload = [
            'iss' => $this->options->jwtIssuer,
            'iat' => $now,
            'exp' => $now + $this->options->jwtTtl,
            'sub' => (string)$userId,
            'type' => 'access',
            'jti' => $this->generateJti(),
        ];

        $payload = array_merge($payload, $additionalClaims);

        $result->setData([
            'accessToken' => JWT::encode($payload, $this->options->jwtSecretKey, $this->options->jwtAlgorithm),
            'accessTokenExpired' => $payload['exp'],
        ]);

        return $result;
    }

    /**
     * Генерация refresh токена
     *
     * @param int $userId ID пользователя
     * @return Result<array<string, string>> JWT refresh токен
     * @throws \InvalidArgumentException
     */
    public function generateRefreshToken(int $userId): Result
    {
        $result = new Result();
        if (!$this->options->enableJwtAuth) {
            return $result->addError(new \Bitrix\Main\Error('JWT disabled'));
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

        $token = JWT::encode($payload, $this->options->jwtSecretKey, $this->options->jwtAlgorithm);

        $result->setData([
            'refreshToken' => $token,
            'refreshTokenExpired' => $payload['exp'],
        ]);

        return $result;
    }

    /**
     * Генерация пары токенов (access + refresh)
     *
     * @param int $userId ID пользователя
     * @param array $additionalClaims Дополнительные данные для access токена
     * @return Result<array<{accessToken: string, refreshToken: string, accessTokenExpired: int, refreshTokenExpired: int}>>
     */
    public function generateTokenPair(int $userId, array $additionalClaims = []): Result
    {
        $result = new Result();

        if (!$this->isEnabled()) {
            return $result->addError(new \Bitrix\Main\Error('JWT disabled'));
        }

        $access = $this->generateAccessToken($userId, $additionalClaims);
        $refresh = $this->generateRefreshToken($userId);

        if (!$access->isSuccess() || !$refresh->isSuccess()) {
            foreach (array_merge($access->getErrors(), $refresh->getErrors()) as $e) {
                $result->addError($e);
            }
            return $result;
        }

        $acc = $access->getData();
        $ref = $refresh->getData();

        $this->sessionRepository->createSession(
            $userId,
            $ref['refreshToken'],
            $additionalClaims['user_agent'] ?? '',
            $additionalClaims['ip'] ?? ''
        );

        return $result->setData([
            'accessToken' => $acc['accessToken'],
            'refreshToken' => $ref['refreshToken'],
            'accessTokenExpired' => $acc['accessTokenExpired'],
            'refreshTokenExpired' => $ref['refreshTokenExpired'],
        ]);
    }

    /**
     * Валидация и декодирование токена
     *
     * @param string $token JWT токен
     * @return Result<array<string,mixed>> Декодированные данные токена
     */
    public function verifyToken(string $token): Result
    {
        $result = new Result();
        if (!$this->options->enableJwtAuth) {
            $result->addError(new \Bitrix\Main\Error('JWT authentication is disabled', 'token'));
            return $result;
        }

        if (empty($this->options->jwtSecretKey)) {
            $result->addError(new \Bitrix\Main\Error('JWT secret key is not configured', 'token'));
            return $result;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->options->jwtSecretKey, $this->options->jwtAlgorithm));
            $result->setData((array)$decoded);
        } catch (ExpiredException $e) {
            $result->addError(new \Bitrix\Main\Error('Token has expired', 'token'));
        } catch (SignatureInvalidException $e) {
            $result->addError(new \Bitrix\Main\Error('Invalid token signature', 'token'));
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error('Invalid token: ' . $e->getMessage(), 'token'));
        }
        return $result;
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
        if (!$decoded->isSuccess()) {
            return 0;
        }
        $decoded = $decoded->getData();
        return (int)$decoded['sub'];
    }

    /**
     * Проверка, является ли токен access токеном
     *
     * @param string $token JWT токен
     * @return bool
     */
    public function isAccessToken(string $token): bool
    {
        $decoded = $this->verifyToken($token);
        if (!$decoded->isSuccess()) {
            return false;
        }
        $decoded = $decoded->getData();
        return isset($decoded['type']) && $decoded['type'] === 'access';
    }

    /**
     * Проверка, является ли токен refresh токеном
     *
     * @param string $token JWT токен
     * @return bool
     */
    public function isRefreshToken(string $token): bool
    {
        $decoded = $this->verifyToken($token);
        if (!$decoded->isSuccess()) {
            return false;
        }
        $decoded = $decoded->getData();
        return isset($decoded['type']) && $decoded['type'] === 'refresh';
    }

    /**
     * Проверка, отозван ли refresh-токен (по jti/хэшу).
     */
    public function isRefreshRevoked(string $refreshToken): bool
    {
        $decoded = $this->verifyToken($refreshToken);
        if (!$decoded->isSuccess()) {
            return true;
        }
        $key = $this->makeRevocationKey($decoded->getData(), $refreshToken);
        try {
            $revokedArr = $this->getCached($this->makeCacheSettingsDTO($key), function () {
                return [
                    'revoked' => false,
                ];
            });
        } catch (\Exception $e) {
            return false;
        }
        return isset($revokedArr['revoked']) && $revokedArr['revoked'] === true;
    }

    /**
     * Обновление токенов по refresh токену
     *
     * @param string $refreshToken Refresh токен
     * @param array $additionalClaims Дополнительные данные для нового access токена
     * @return Result{access: string, refresh: string, accessTokenExpired: int, refreshTokenExpired: int}
     * @throws \InvalidArgumentException
     */
    public function refreshTokens(string $refreshToken, array $additionalClaims = []): Result
    {
        $result = new Result();

        // 1. Валидация структуры токена
        if (!$this->isRefreshToken($refreshToken)) {
            return $result->addError(new Error('Invalid refresh token'));
        }

        // 2. Проверка ревокации через кэш (blacklist)
        if ($this->isRefreshRevoked($refreshToken)) {
            return $result->addError(new Error('Refresh token revoked (cache)'));
        }

        // 3. Проверка существования активной сессии
        $session = $this->sessionRepository->findByToken($refreshToken);
        if (!$session || $session['REVOKED'] === 'Y') {
            return $result->addError(new Error('Session revoked (db)'));
        }

        // 4. Декодирование токена
        $decoded = $this->verifyToken($refreshToken);
        if (!$decoded->isSuccess()) {
            return $decoded;
        }

        $data = $decoded->getData();
        $userId = (int)$data['sub'];

        $sessionId = $session['ID'];
        if ($sessionId) {
            $this->sessionRepository->touchSession((int)$sessionId);
        }

        return $this->generateTokenPair($userId, $additionalClaims);
    }

    /**
     * Отзыв (ревокация) refresh токена. Нужен для logout или ручного отзыва.
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        $decoded = $this->verifyToken($refreshToken);
        if (!$decoded->isSuccess()) {
            return;
        }

        $data = $decoded->getData();
        $key = $this->makeRevocationKey($data, $refreshToken);
        $this->getCached($this->makeCacheSettingsDTO($key), fn() => ['revoked' => true]);

        $this->sessionRepository->revokeByToken($refreshToken);
    }

    public function revokeAllSessions(int $userId): void
    {
        $this->sessionRepository->revokeAll($userId);
    }

    public function revokeAllExcept(string $refreshToken): void
    {
        $decoded = $this->verifyToken($refreshToken);
        if (!$decoded->isSuccess()) return;
        $data = $decoded->getData();

        $this->sessionRepository->revokeAllExcept(
            (int)$data['sub'],
            $refreshToken
        );
    }

    public function getUserSessions(int $userId): array
    {
        return $this->sessionRepository->findSessionsByUser($userId);
    }

    /**
     * Получить claims в виде массива (helper).
     *
     * @return Result<array<string,mixed>>
     */
    public function getTokenClaims(string $token): Result
    {
        $decoded = $this->verifyToken($token);
        if (!$decoded->isSuccess()) {
            return $decoded;
        }
        $result = new Result();
        $decoded = $decoded->getData();
        $result->setData(Json::decode(Json::encode($decoded)));
        return $result;
    }

    /**
     * Проверка, включена ли JWT авторизация
     */
    public function isEnabled(): bool
    {
        return $this->options->enableJwtAuth && !empty($this->options->jwtSecretKey);
    }

    private function generateJti(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Ключ для black/allow-листа: по возможности jti, иначе sha256 от всего токена.
     */
    private function makeRevocationKey(array $decoded, string $rawToken): string
    {
        /** @var string|null $jti */
        $jti = isset($decoded['jti']) && is_string($decoded['jti']) ? $decoded['jti'] : null;
        return $jti ?: hash('sha256', $rawToken);
    }
}
