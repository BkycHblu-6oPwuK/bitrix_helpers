<?php
declare(strict_types=1);

namespace Beeralex\User\Controller;

use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\JwtTokenManager;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Error;

/**
 * REST API контроллер для JWT авторизации
 * 
 * URL: /rest/beeralex.user.auth/
 * 
 * Примеры запросов:
 * 
 * POST /rest/beeralex.user.auth/login
 * {
 *   "type": "email",
 *   "email": "user@example.com",
 *   "password": "password123"
 * }
 * 
 * POST /rest/beeralex.user.auth/refresh
 * {
 *   "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
 * }
 * 
 * GET /rest/beeralex.user.auth/profile
 * Headers: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
 * 
 * POST /rest/beeralex.user.auth/logout
 * Headers: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
 */
class AuthController extends Controller
{
    private AuthManager $authManager;
    private JwtTokenManager $jwtManager;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->authManager = service(AuthManager::class);
        $this->jwtManager = service(JwtTokenManager::class);
    }

    /**
     * Настройка действий контроллера
     */
    public function configureActions(): array
    {
        return [
            'login' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
            'refresh' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
            'profile' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                    new HttpMethod([HttpMethod::METHOD_GET]),
                ],
            ],
            'logout' => [
                'prefilters' => [
                    new JwtAuthFilter(['optional' => true]),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
            'verify' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                    new HttpMethod([HttpMethod::METHOD_GET]),
                ],
            ],
        ];
    }

    /**
     * Авторизация с генерацией JWT токенов
     * 
     * @param string $type Тип авторизации (email, phone, social)
     * @param string $email Email пользователя
     * @param string $password Пароль
     * @param string|null $phone Телефон (опционально)
     * @return array
     */
    public function loginAction(string $type = 'email', string $email = '', string $password = '', ?string $phone = null): array
    {
        try {
            // Создаем DTO для пользователя
            $userDto = new class($email, $password, $phone) extends \Beeralex\User\Dto\BaseUserDto {
                public function __construct(string $email, string $password, ?string $phone)
                {
                    $this->email = $email;
                    $this->password = $password;
                    $this->phone = $phone;
                }
            };

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
                'email' => $email,
                'auth_type' => $type,
            ]);

            return [
                'tokens' => $tokens,
                'user' => [
                    'id' => $userId,
                    'email' => $USER->GetEmail(),
                    'name' => $USER->GetFullName(),
                    'login' => $USER->GetLogin(),
                ],
            ];

        } catch (\Exception $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Обновление токенов по refresh токену
     * 
     * @param string $refreshToken Refresh токен
     * @return array
     */
    public function refreshAction(string $refreshToken): array
    {
        try {
            if (empty($refreshToken)) {
                throw new \InvalidArgumentException('Refresh token is required');
            }

            $tokens = $this->authManager->refreshTokens($refreshToken);

            return [
                'tokens' => $tokens,
            ];

        } catch (\Exception $e) {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * Получение профиля текущего пользователя
     * Требует JWT авторизацию
     * 
     * @return array
     */
    public function profileAction(): array
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            $this->addError(new Error('User not authorized'));
            return [];
        }

        $userId = $USER->GetID();
        $user = \CUser::GetByID($userId)->Fetch();

        return [
            'user' => [
                'id' => $user['ID'],
                'login' => $user['LOGIN'],
                'email' => $user['EMAIL'],
                'name' => $user['NAME'],
                'lastName' => $user['LAST_NAME'],
                'secondName' => $user['SECOND_NAME'],
                'personalPhone' => $user['PERSONAL_PHONE'],
                'personalPhoto' => $user['PERSONAL_PHOTO'],
            ],
        ];
    }

    /**
     * Проверка валидности токена
     * Требует JWT авторизацию
     * 
     * @return array
     */
    public function verifyAction(): array
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            $this->addError(new Error('Token is invalid'));
            return ['valid' => false];
        }

        return [
            'valid' => true,
            'userId' => $USER->GetID(),
        ];
    }

    /**
     * Выход из системы
     * Токен опциональный
     * 
     * @return array
     */
    public function logoutAction(): array
    {
        global $USER;
        
        if ($USER->IsAuthorized()) {
            $USER->Logout();
        }

        return [
            'success' => true,
            'message' => 'Logged out successfully',
        ];
    }
}
