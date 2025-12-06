<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;
use Beeralex\User\Auth\JwtTokenManager;
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

            $token = static::extractJwtToken($request);
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
                if ($USER instanceof \CUser && (!$USER->IsAuthorized() || $USER->GetID() != $userId)) {
                    service(EmptyAuthentificator::class)->authorizeByUserId($userId);
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Извлечение JWT токена из запроса
     */
    private static function extractJwtToken(\Bitrix\Main\HttpRequest $request): ?string
    {
        $authHeader = static::getAuthorizationHeader();
        if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        $token = $request->get('access_token') ?? $request->get('token');
        if ($token) {
            return $token;
        }

        return null;
    }

    /**
     * Получение заголовка Authorization
     */
    private static function getAuthorizationHeader(): ?string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return null;
    }
}
