<?php

namespace Beeralex\User\Controllers;

use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\SocialAuthenticatorFactory;
use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\User;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Throwable;

/**
 * Контроллер для авторизации через социальные сети Bitrix.
 *
 * Пример маршрутов:
 *   /api/auth/social/redirect?provider=google
 *   /api/auth/social/callback?provider=google
 */
class AuthController extends Controller
{
    protected AuthManager $authManager;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->authManager = service(AuthManager::class);
    }

    protected function getDefaultPreFilters()
    {
        return [];
    }

    /**
     * 1️⃣ Возвращает ссылку на авторизацию соцсети
     * или делает редирект.
     *
     * GET /api/auth/social/redirect?provider=google
     */
    public function redirectAction(string $provider)
    {
        $url = $this->authManager->getAuthorizationUrl($provider);
        return [
            'redirect_url' => $url,
        ];
    }

    public function callbackAction(string $provider)
    {
        try {
            $this->authManager->attempt($provider, null);
            $user = User::current();
            if ($user->isAuthorized()) {
                return [
                    'status' => 'success',
                    'user_id' => $user->getId(),
                    'user_login' => $user->getEmail(),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Пользователь не авторизован после callback.',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
