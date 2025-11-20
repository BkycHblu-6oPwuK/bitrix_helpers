<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers\User;

use Beeralex\Core\Http\Controllers\ApiController;
use Bitrix\Main\Error;
use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Beeralex\User\Service\AuthService;
use Beeralex\User\Dto\AuthCredentialsDto;

/**
 * REST API контроллер для аутентификации
 * 
 * Endpoints:
 * - POST /api/auth/login - Логин с выдачей JWT токенов
 * - POST /api/auth/refresh - Обновление токенов
 * - POST /api/auth/register - Регистрация нового пользователя
 * - GET /api/auth/profile - Получение профиля (требует авторизации)
 * - POST /api/auth/logout - Выход с отзывом refresh токена
 * - GET /api/auth/methods - Список доступных методов авторизации
 */
class AuthController extends ApiController
{
    protected AuthService $authService;

    protected function init(): void
    {
        parent::init();
        
        // Получаем AuthService из DI контейнера
        $this->authService = \service(AuthService::class);
    }

    /**
     * Настройка фильтров для действий контроллера
     */
    public function configureActions(): array
    {
        return [
            'login' => [
                'prefilters' => [],
            ],
            'refresh' => [
                'prefilters' => [],
            ],
            'register' => [
                'prefilters' => [],
            ],
            'profile' => [
                'prefilters' => [],
            ],
            'logout' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                ],
            ],
            'methods' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Логин пользователя с выдачей JWT токенов
     * 
     * POST /api/auth/login
     * Body: {
     *   "type": "local",
     *   "email": "user@example.com",
     *   "password": "password"
     * }
     * 
     * @param AuthCredentialsDto $credentials DTO с данными запроса
     * @return array
     */
    public function loginAction(AuthCredentialsDto $credentials): array
    {
        try {
            $metadata = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];

            $result = $this->authService->login($credentials, $metadata);

            if(!$result->isSuccess()) {
                $this->addErrors($result->getErrors());
                return [];
            }

            return $result->getData();
            
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Обновление пары токенов по refresh токену
     * 
     * POST /api/auth/refresh
     * Body: {
     *   "refreshToken": "..."
     * }
     * 
     * @param string $refreshToken Refresh токен
     * @return array
     */
    public function refreshAction(string $refreshToken): array
    {
        try {
            $result = $this->authService->refreshTokens($refreshToken);

            return $result;
            
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Регистрация нового пользователя
     * 
     * POST /api/auth/register
     * Body: {
     *   "type": "local",
     *   "email": "user@example.com",
     *   "password": "password",
     *   "name": "John Doe"
     * }
     * 
     * @param AuthCredentialsDto $credentials DTO с данными запроса
     * @return array
     */
    public function registerAction(AuthCredentialsDto $credentials): array
    {
        try {
            $result = $this->authService->register($credentials);

            if(!$result->isSuccess()) {
                $this->addErrors($result->getErrors());
                return [];
            }

            return $result->getData();
            
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Выход с отзывом refresh токена
     * Требует JWT авторизации
     * 
     * POST /api/auth/logout
     * Headers: Authorization: Bearer <access_token>
     * Body: {
     *   "refreshToken": "...",
     *   "logoutFromBitrix": true
     * }
     * 
     * @param string|null $refreshToken Refresh токен для отзыва
     * @param bool $logoutFromBitrix Разлогинить из Bitrix сессии
     * @return array
     */
    public function logoutAction(?string $refreshToken = null, bool $logoutFromBitrix = true): array
    {
        try {
            $this->authService->logout($refreshToken, $logoutFromBitrix);

            return [
                'message' => 'Logout successful',
            ];
            
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Получение списка доступных методов аутентификации
     * 
     * GET /api/auth/methods
     * 
     * @return array
     */
    public function methodsAction(): array
    {
        try {
            $methods = $this->authService->getAvailableAuthMethods();
            
            return $methods;
            
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }
}
