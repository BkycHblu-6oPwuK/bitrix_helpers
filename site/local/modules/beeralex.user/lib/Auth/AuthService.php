<?php

declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\JwtTokenManager;
use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

/**
 * Сервис для работы с аутентификацией
 * Инкапсулирует бизнес-логику авторизации, регистрации и работы с токенами
 */
class AuthService
{
    public function __construct(
        protected readonly AuthManager $authManager,
        protected readonly JwtTokenManager $jwtManager
    ) {}

    /**
     * Логин пользователя с выдачей JWT токенов
     * 
     * @param AuthCredentialsDto $credentials DTO с данными для входа
     * @param array $metadata Дополнительные данные (ip, user_agent)
     * @return Result{userId: int, authType: string, accessToken: string, refreshToken: string|null, accessTokenExpired: int|null, refreshTokenExpired: int|null}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function login(AuthCredentialsDto $credentials, array $metadata = []): Result
    {
        $result = new Result();
        $authResult = $this->authManager->authenticate($credentials->type, $credentials);

        if (!$authResult->isSuccess()) {
            return $authResult;
        }

        $authData = $authResult->getData();
        $resultData = [
            'userId' => $authData['userId'],
            'authType' => $authData['authType'],
        ];

        // Если JWT включен, генерируем и добавляем токены
        if ($this->jwtManager->isEnabled()) {
            $tokensResult = $this->jwtManager->generateTokenPair(
                $authData['userId'],
                array_merge([
                    'auth_type' => $authData['authType'],
                    'email' => $authData['email'],
                ], $metadata)
            );
            $tokens = $tokensResult->getData();
            $resultData['accessToken'] = $tokens['accessToken'];
            $resultData['refreshToken'] = $tokens['refreshToken'];
            $resultData['accessTokenExpired'] = $tokens['accessTokenExpired'];
            $resultData['refreshTokenExpired'] = $tokens['refreshTokenExpired'];
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * Регистрация нового пользователя с автоматическим логином
     * 
     * @param AuthCredentialsDto $credentials DTO с данными для регистрации
     * @return Result{userId: int, accessToken: string, refreshToken: string|null}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        // Регистрируем пользователя через AuthManager
        $registerResult = $this->authManager->register($credentials->type, $credentials);
        if (!$registerResult->isSuccess()) {
            return $registerResult;
        }

        // Аутентифицируем после успешной регистрации
        $authResult = $this->authManager->authenticate($credentials->type, $credentials);
        if (!$authResult->isSuccess()) {
            return $authResult;
        }

        $authData = $authResult->getData();
        $resultData = [
            'userId' => $authData['userId'],
        ];

        if ($this->jwtManager->isEnabled()) {
            $tokensResult = $this->jwtManager->generateTokenPair(
                $authData['userId'],
                [
                    'auth_type' => $authData['authType'],
                    'email' => $authData['email'],
                ]
            );
            $tokens = $tokensResult->getData();
            $resultData['accessToken'] = $tokens['accessToken'];
            $resultData['refreshToken'] = $tokens['refreshToken'];
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * Обновление пары токенов по refresh токену
     * 
     * @param string $refreshToken Refresh токен
     * @return array{accessToken: string, refreshToken: string|null}
     * @throws \InvalidArgumentException
     */
    public function refreshTokens(string $refreshToken): array
    {
        $tokens = $this->jwtManager->refreshTokens($refreshToken);

        $result = [
            'accessToken' => $tokens['access'],
            'refreshToken' => $tokens['refresh'],
        ];
        return $result;
    }

    /**
     * Выход пользователя с отзывом refresh токена
     * 
     * @param string|null $refreshToken Refresh токен для отзыва
     * @param bool $logoutFromBitrix Разлогинить из Bitrix сессии
     * @return void
     */
    public function logout(?string $refreshToken = null, bool $logoutFromBitrix = false): void
    {
        if ($refreshToken) {
            try {
                $this->jwtManager->revokeRefreshToken($refreshToken);
            } catch (\Throwable $e) {
                // Токен может быть уже невалидным - это не критично
            }
        }

        // Разлогиниваем из Bitrix сессии если нужно
        if ($logoutFromBitrix) {
            global $USER;
            if (isset($USER) && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized()) {
                $USER->Logout();
            }
        }
    }

    /**
     * Получение списка доступных методов аутентификации
     * 
     * @return array Массив методов с их настройками
     */
    public function getAvailableAuthMethods(): array
    {
        $methods = $this->authManager->getAvailable();

        $result = [];
        foreach ($methods as $method) {
            try {
                $authDataResult = $this->authManager->getAuthorizationUrlOrHtml($method);
                if (!$authDataResult->isSuccess()) {
                    continue;
                }
                $authData = $authDataResult->getData();
                $result[] = [
                    'type' => $method,
                    'authType' => $authData['type'] ?? null,
                    'value' => $authData['value'] ?? null,
                ];
            } catch (\Throwable) {
                continue;
            }
        }
        return $result;
    }

    /**
     * Верификация access токена
     * 
     * @param string $accessToken Access токен
     * @return Result<array{userId: int, claims: array}>
     * @throws \InvalidArgumentException
     */
    public function verifyAccessToken(string $accessToken): Result
    {
        $result = new Result();
        if (!$this->jwtManager->isAccessToken($accessToken)) {
            $result->addError(new \Bitrix\Main\Error('Invalid access token', 'accessToken'));
            return $result;
        }

        $userId = $this->jwtManager->getUserIdFromToken($accessToken);
        $claims = $this->jwtManager->getTokenClaims($accessToken);

        if (!$claims->isSuccess()) {
            return $claims;
        }

        return $result->setData([
            'userId' => $userId,
            'claims' => $claims->getData(),
        ]);
    }

    /**
     * Проверка, включена ли JWT авторизация
     * 
     * @return bool
     */
    public function isJwtEnabled(): bool
    {
        return $this->jwtManager->isEnabled();
    }
}
