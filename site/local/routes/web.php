<?

use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\User\UserController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->get(
        '/',
        [MainController::class, 'index']
    );
    $routes->get(
        '/man/',
        [MainController::class, 'man']
    );
    $routes->get(
        '/woman/',
        [MainController::class, 'woman']
    );
    $routes->post(
        '/oauth/token/',
        [Beeralex\Oauth2\Controllers\AccessTokensController::class, 'issueToken']
    );
    $routes->post(
        '/test/',
        [MainController::class, 'test']
    );
    $routes->post('/user/register/', [UserController::class, 'register']);
    $routes->any('/user/auth/telegram/', [UserController::class, 'authByTelegram']);
    $routes->get(
        '/oauth/authorize/',
        [Beeralex\Oauth2\Controllers\AuthorizationController::class, 'authorize']
    );
};
