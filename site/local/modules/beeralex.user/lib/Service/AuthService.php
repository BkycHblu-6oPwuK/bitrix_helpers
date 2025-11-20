<?php
declare(strict_types=1);

namespace Beeralex\User\Service;

use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\JwtTokenManager;
use Beeralex\User\Dto\AuthCredentialsDto;
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
     * @return Result{userId: int, authType: string, accessToken: string, refreshToken: string|null}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function login(AuthCredentialsDto $credentials, array $metadata = []): Result
    {
        $result = new Result();
        $authResult = $this->authManager->authenticate($credentials->type, $credentials);

        if(!$authResult->isSuccess()) {
            return $authResult;
        }

        // Проверяем, что JWT включен
        if (!$this->jwtManager->isEnabled()) {
            $result->addError(new \Bitrix\Main\Error('JWT issuing is disabled by configuration'));
            return $result;
        }

        $authResult = $authResult->getData();

        // Генерируем JWT токены
        $tokens = $this->jwtManager->generateTokenPair(
            $authResult['userId'],
            array_merge([
                'auth_type' => $authResult['authType'],
                'email' => $authResult['email'],
            ], $metadata)
        );

        $result->setData([
            'userId' => $authResult['userId'],
            'authType' => $authResult['authType'],
            'accessToken' => $tokens['access'],
            'refreshToken' => $tokens['refresh'],
        ]);

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
        $this->authManager->register($credentials->type, $credentials);

        // Аутентифицируем после успешной регистрации
        $authResult = $this->authManager->authenticate($credentials->type, $credentials);

        // Проверяем, что JWT включен
        if (!$this->jwtManager->isEnabled()) {
            $result->addError(new \Bitrix\Main\Error('JWT issuing is disabled by configuration'));
            return $result;
        }

        // Генерируем JWT токены
        $tokens = $this->jwtManager->generateTokenPair(
            $authResult['userId'],
            [
                'auth_type' => $authResult['authType'],
                'email' => $authResult['email'],
            ]
        );

        $result->setData([
            'userId' => $authResult['userId'],
            'accessToken' => $tokens['access'],
            'refreshToken' => $tokens['refresh'],
        ]);

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
        // Работа с токенами - ответственность JwtTokenManager
        $tokens = $this->jwtManager->refreshTokens($refreshToken);

        $result = [
            'accessToken' => $tokens['access'],
            'refreshToken' => $tokens['refresh'],
        ];
        return $result;
    }

    /**
     * Получение данных профиля пользователя
     * 
     * @param int $userId ID пользователя
     * @return array{id: int, email: string, name: string|null, lastName: string|null, login: string, phone: string|null}
     * @throws \RuntimeException
     */
    public function getUserProfile(int $userId): array
    {
        $rsUser = \CUser::GetByID($userId);
        $arUser = $rsUser->Fetch();

        if (!$arUser) {
            throw new \RuntimeException("User with ID {$userId} not found");
        }

        return [
            'id' => (int)$arUser['ID'],
            'email' => $arUser['EMAIL'],
            'name' => $arUser['NAME'] ?: null,
            'lastName' => $arUser['LAST_NAME'] ?: null,
            'login' => $arUser['LOGIN'],
            'phone' => $arUser['PERSONAL_PHONE'] ?: null,
        ];
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
        // Отзываем refresh токен если передан
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
                $authData = $this->authManager->getAuthorizationUrlOrHtml($method);
                $result[] = [
                    'type' => $method,
                    'authType' => $authData['type'] ?? null,
                    'value' => $authData['value'] ?? null,
                ];
            } catch (\Throwable) {
                // Пропускаем методы, которые не смогли вернуть данные
                continue;
            }
        }
        return $result;
    }

    /**
     * Верификация access токена
     * 
     * @param string $accessToken Access токен
     * @return array{userId: int, claims: array}
     * @throws \InvalidArgumentException
     */
    public function verifyAccessToken(string $accessToken): array
    {
        if (!$this->jwtManager->isAccessToken($accessToken)) {
            throw new \InvalidArgumentException('Invalid access token');
        }

        $userId = $this->jwtManager->getUserIdFromToken($accessToken);
        $claims = $this->jwtManager->getTokenClaims($accessToken);

        return [
            'userId' => $userId,
            'claims' => $claims,
        ];
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
