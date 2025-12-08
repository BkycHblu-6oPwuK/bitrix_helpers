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
        if ($token) {
            $verify = $manager->verifyToken($token);

            if ($verify->isSuccess()) {
                $fuserId = $verify->getData()['fuserId'];
                $_SESSION['SALE_USER_ID'] = $fuserId;
                return;
            }

            if ($verify->getErrorCollection()->getErrorByCode('expired')) {

                $fuserId = $verify->getData()['fuserId'];

                if ($fuserId > 0) {
                    $_SESSION['SALE_USER_ID'] = $fuserId;

                    $newToken = $manager->generateToken($fuserId)->getData()['fuserToken'];
                    Context::getCurrent()->getResponse()->addHeader('X-New-Fuser-Token', $newToken);

                    return;
                }
            }
        }
        $newFuserId = Fuser::getId();
        $newToken = $manager->generateToken($newFuserId)->getData()['fuserToken'];
        $_SESSION['SALE_USER_ID'] = $newFuserId;
        Context::getCurrent()->getResponse()->addHeader('X-New-Fuser-Token', $newToken);
    }
}
