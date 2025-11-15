<?php
declare(strict_types=1);
namespace Beeralex\Api\V1\Controllers\User;

use Beeralex\User\Auth\AuthManager;
use Beeralex\User\User;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
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
        $urlOrHtml = $this->authManager->getAuthorizationUrlOrHtml($provider);
        return $urlOrHtml;
    }

    public function callbackAction(string $provider)
    {
        try {
            $this->authManager->attempt($provider, null);
            $user = User::current();
            if ($user->isAuthorized()) {
                return [
                    'user_id' => $user->getId(),
                    'user_login' => $user->getEmail(),
                ];
            }
        } catch (Throwable $e) {
            $this->addError(Error::createFromThrowable($e));
        }
    }
}
