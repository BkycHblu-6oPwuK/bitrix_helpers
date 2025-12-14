<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\Api\Domain\User\UserService;
use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;
use Beeralex\User\Auth\JwtTokenManager;
use Beeralex\User\Auth\Session\UserSessionRepository;
use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;

/**
 * Обработчик для авторизации по JWT токену в начале запроса
 */
class JwtTokenHandler
{
    public static function handle(HttpRequest $request): void
    {
        if (!Loader::includeModule('beeralex.user')) {
            return;
        }

        try {
            $jwtManager = service(JwtTokenManager::class);

            if (!$jwtManager->isEnabled()) {
                return;
            }

            $token = service(UserService::class)->extractJwtToken($request);
            if (!$token) {
                return;
            }

            $result = $jwtManager->verifyToken($token);
            if (!$result->isSuccess()) {
                return;
            }

            $decoded = $result->getData();

            if (!$jwtManager->isAccessToken($token)) {
                return;
            }

            $userId = (int)$decoded['sub'];
            if ($userId > 0) {
                global $USER;
                if (!($USER instanceof \CUser) || (!$USER->IsAuthorized() || $USER->GetID() != $userId)) {
                    service(EmptyAuthentificator::class)->authorizeByUserId($userId);
                }
                static::updateLastActivity($request);
            }
        } catch (\Exception $e) {
        }
    }

    public static function updateLastActivity(HttpRequest $request): void
    {
        $refreshToken = service(UserService::class)->extractRefreshToken($request);
        $jwt = service(JwtTokenManager::class);
        if (!$refreshToken || !$jwt->isRefreshToken($refreshToken)) {
            return;
        }
        $sessions = service(UserSessionRepository::class);
        $session = $sessions->findByToken($refreshToken);
        if (!$session) {
            return;
        }
        $lastTs = $session['LAST_ACTIVITY']->getTimestamp();
        if (time() - $lastTs >= 300) {
            $sessions->touchSession((int)$session['ID']);
        }
    }
}
