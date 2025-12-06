<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\User\Auth\FuserTokenManager;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Sale\Fuser;

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

        $headers = $request->getHeaders();
        $token = $headers->get('X-Fuser-Token');

        $manager = service(FuserTokenManager::class);

        $fuserId = $token ? $manager->getFuserId($token) : 0;

        if ($fuserId > 0) {
            $_SESSION['SALE_USER_ID'] = $fuserId;
            return;
        }

        $fuserId = Fuser::getId();
        $result = $manager->generateToken($fuserId);

        if(!$result->isSuccess()) {
            return;
        }

        $newToken = $result->getData()['fuserToken'];

        $_SESSION['SALE_USER_ID'] = $fuserId;

        Context::getCurrent()->getResponse()->addHeader('X-New-Fuser-Token', $newToken);
    }
}
