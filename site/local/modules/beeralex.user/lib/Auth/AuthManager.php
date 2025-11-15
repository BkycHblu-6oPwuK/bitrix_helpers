<?php
declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\SocialAuthenticatorFactory;
use Beeralex\User\Dto\BaseUserDto;

/**
 * Тонкий менеджер аутентификации для Bitrix:
 * - Аутентификаторы сами решают, авторизовывать ли через $USER->Authorize()
 * - AuthManager координирует флоу и при необходимости выдаёт JWT
 */
class AuthManager
{
    /**
     * ключом выступает название интерфейса/ключ аутентификатора
     * @param array<string, AuthenticatorContract> $authenticators
     */
    public function __construct(
        public readonly array $authenticators,
        protected readonly JwtTokenManager $jwtManager
    ) {}

    /**
     * Базовая аутентификация без выпуска токенов.
     * Возвращает userId и тип аутентификатора.
     *
     * @return array{userId:int, auth_type:string}
     * @throws \Throwable
     */
    public function authenticateOnly(string $type, ?BaseUserDto $userDto = null): array
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            throw new \InvalidArgumentException("Unknown auth type: {$type}");
        }
        if (!$authenticator->isService() && $userDto === null) {
            throw new \InvalidArgumentException("User data must be provided for local authenticators");
        }

        // Аутентификатор сам решает — поднимать сессию ($USER->Authorize) или нет.
        $authenticator->authenticate($userDto);

        $userId = $this->resolveUserId();
        if (!$userId) {
            throw new \RuntimeException('Authenticated userId cannot be determined');
        }

        return ['userId' => $userId, 'auth_type' => $type];
    }

    /**
     * Аутентификация с выдачей пары JWT (token-only флоу).
     * Если аутентификатор по пути поднял сессию — это его решение.
     *
     * @return array{
     *   userId:int,
     *   auth_type:string,
     *   tokens: array{access:string, refresh:string|null}
     * }
     * @throws \Throwable
     */
    public function loginToken(string $type, ?BaseUserDto $userDto = null, array $extraClaims = []): array
    {
        $auth = $this->authenticateOnly($type, $userDto);

        if (!$this->jwtManager->isEnabled()) {
            throw new \RuntimeException('JWT issuing is disabled by configuration');
        }

        $tokens = $this->jwtManager->generateTokenPair($auth['userId'], array_merge([
            'auth_type' => $auth['auth_type'],
            'email' => $userDto?->email ?? null,
        ], $extraClaims));

        return [
            'userId' => $auth['userId'],
            'auth_type' => $auth['auth_type'],
            'tokens' => $tokens,
        ];
    }

    /**
     * Аутентификация + выдача JWT, оставлено для совместимости “session+token” флоу,
     * но управление сессией не делает (её поднимет аутентификатор при необходимости).
     */
    public function loginBoth(string $type, ?BaseUserDto $userDto = null, array $extraClaims = []): array
    {
        return $this->loginToken($type, $userDto, $extraClaims);
    }

    /**
     * Верификация только по access JWT (без изменения сессии).
     *
     * @return array{userId:int, auth_type:string}
     */
    public function loginByToken(string $accessToken): array
    {
        if (!$this->jwtManager->isEnabled()) {
            throw new \RuntimeException('JWT authentication is disabled or not configured');
        }
        if (!$this->jwtManager->isAccessToken($accessToken)) {
            throw new \InvalidArgumentException('Invalid access token');
        }

        $userId = $this->jwtManager->getUserIdFromToken($accessToken);
        if (!$userId) {
            throw new \RuntimeException('Cannot resolve user from access token');
        }

        return [
            'userId' => $userId,
            'auth_type' => 'bearer',
        ];
    }

    /**
     * Обновление пары токенов по refresh.
     * @return array{access:string, refresh:string|null}
     */
    public function refreshTokens(string $refreshToken): array
    {
        return $this->jwtManager->refreshTokens($refreshToken);
    }

    /**
     * Явная генерация пары токенов без логина (например, после MFA).
     * @return array{access:string, refresh:string|null}
     */
    public function generateTokens(int $userId, array $additionalClaims = []): array
    {
        return $this->jwtManager->generateTokenPair($userId, $additionalClaims);
    }

    public function register(string $type, BaseUserDto $userDto): void
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }
        $this->authenticators[$type]->register($userDto);
    }

    /**
     * @return array{type:string, value:string}
     */
    public function getAuthorizationUrlOrHtml(string $type): array
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }
        return $this->authenticators[$type]->getAuthorizationUrlOrHtml() ?? [];
    }

    /**
     * Список доступных методов.
     * @return string[]
     */
    public function getAvailable(): array
    {
        return array_keys($this->authenticators);
    }

    // -------------------- Вспомогательное --------------------

    protected function bitrixCurrentUserId(): ?int
    {
        global $USER;
        if (isset($USER) && method_exists($USER, 'GetID') && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized()) {
            return (int)$USER->GetID();
        }
        return null;
    }

    protected function resolveUserId(): ?int
    {
        return $this->bitrixCurrentUserId();
    }
}