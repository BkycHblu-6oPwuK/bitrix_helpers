<?php
declare(strict_types=1);

namespace Beeralex\User\Api;

use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\JwtTokenManager;
use Beeralex\User\Dto\BaseUserDto;
use Bitrix\Main\Context;

/**
 * API контроллер для работы с JWT авторизацией
 * 
 * Примеры использования:
 * 
 * POST /local/api/auth/login
 * {
 *   "type": "email",
 *   "email": "user@example.com",
 *   "password": "password123"
 * }
 * 
 * POST /local/api/auth/refresh
 * {
 *   "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
 * }
 * 
 * GET /local/api/auth/verify
 * Headers: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
 */
class AuthApiController
{
    public function __construct(
        private readonly AuthManager $authManager,
        private readonly JwtTokenManager $jwtManager
    ) {}

    /**
     * Авторизация с генерацией JWT токенов
     * 
     * @param array $data Данные для авторизации
     * @return array
     */
    public function login(array $data): array
    {
        try {
            $type = $data['type'] ?? 'email';
            
            // Создаем DTO для пользователя
            $userDto = $this->createUserDto($data);
            
            // Выполняем авторизацию
            $this->authManager->attempt($type, $userDto);
            
            // Получаем ID авторизованного пользователя
            global $USER;
            if (!$USER->IsAuthorized()) {
                throw new \RuntimeException('Authorization failed');
            }
            
            $userId = (int)$USER->GetID();
            
            // Генерируем токены
            $tokens = $this->authManager->generateTokens($userId, [
                'email' => $data['email'] ?? '',
                'auth_type' => $type,
            ]);
            
            return [
                'success' => true,
                'tokens' => $tokens,
                'user' => [
                    'id' => $userId,
                    'email' => $USER->GetEmail(),
                    'name' => $USER->GetFullName(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Обновление токенов по refresh токену
     * 
     * @param array $data Данные с refresh токеном
     * @return array
     */
    public function refresh(array $data): array
    {
        try {
            $refreshToken = $data['refresh_token'] ?? '';
            
            if (empty($refreshToken)) {
                throw new \InvalidArgumentException('Refresh token is required');
            }
            
            $tokens = $this->authManager->refreshTokens($refreshToken);
            
            return [
                'success' => true,
                'tokens' => $tokens,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Проверка токена
     * 
     * @return array
     */
    public function verify(): array
    {
        try {
            $request = Context::getCurrent()->getRequest();
            $authHeader = $request->getHeader('Authorization');
            
            if (!$authHeader) {
                throw new \RuntimeException('Authorization header is missing');
            }
            
            $token = $this->jwtManager->extractTokenFromHeader($authHeader);
            if (!$token) {
                throw new \RuntimeException('Invalid authorization header format');
            }
            
            $decoded = $this->jwtManager->verifyToken($token);
            
            return [
                'success' => true,
                'valid' => true,
                'user_id' => $decoded->sub,
                'expires_at' => $decoded->exp,
                'issued_at' => $decoded->iat,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Генерация токенов для существующего пользователя
     * 
     * @param int $userId ID пользователя
     * @param array $additionalClaims Дополнительные данные
     * @return array
     */
    public function generateTokensForUser(int $userId, array $additionalClaims = []): array
    {
        try {
            $tokens = $this->authManager->generateTokens($userId, $additionalClaims);
            
            return [
                'success' => true,
                'tokens' => $tokens,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Создание DTO из массива данных
     * 
     * @param array $data
     * @return BaseUserDto
     */
    private function createUserDto(array $data): BaseUserDto
    {
        // Здесь нужно создать конкретный DTO в зависимости от типа авторизации
        // Это упрощенный пример
        return new class($data) extends BaseUserDto {
            public function __construct(array $data)
            {
                $this->email = $data['email'] ?? '';
                $this->password = $data['password'] ?? '';
                $this->phone = $data['phone'] ?? null;
            }
        };
    }
}
