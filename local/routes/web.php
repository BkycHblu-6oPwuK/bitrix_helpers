<?

use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\MainController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->get(
        '/catalog/{search}',
        [CatalogController::class, 'index']
    )->where('search', '.*');
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
    $routes->get(
        '/oauth/authorize/',
        [Beeralex\Oauth2\Controllers\AuthorizationController::class, 'authorize']
    );
};
