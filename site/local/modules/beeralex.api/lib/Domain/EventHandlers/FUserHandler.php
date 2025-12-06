<?

namespace Beeralex\Api\Domain\EventHandlers;

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;

/**
 * Обработчик для сохранения Fuser ID из заголовка запроса
 */
class FUserHandler
{
    public static function handle(HttpRequest $request): void
    {
        global $USER;
        if ($USER instanceof \CUser && $USER->IsAuthorized()) {
            return;
        }
        if (!Loader::includeModule('beeralex.user') || !Loader::includeModule('sale')) {
            return;
        }

        try {
            $headers = $request->getHeaders();
            $fuserToken = $headers->get('X-Fuser-Token');

            if (!$fuserToken) {
                return;
            }
            $fuserManager = service(\Beeralex\User\Auth\FuserTokenManager::class);
            $fuserId = $fuserManager->getFuserId($fuserToken);

            if ($fuserId > 0) {
                $_SESSION['SALE_USER_ID'] = $fuserId;
            }
        } catch (\Exception $e) {
        }
    }
}
