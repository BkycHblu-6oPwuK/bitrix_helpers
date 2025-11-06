<?

use Beeralex\Api\V1\Controllers\CatalogController;
use Beeralex\Api\V1\Controllers\MainController;
use Beeralex\User\Controllers\AuthController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->prefix('api')->group(function (RoutingConfigurator $routes) {
        $routes->prefix('v1')->group(function(RoutingConfigurator $routes) {
            $routes->any('get-content', [MainController::class, 'getContent']);
            $routes->any('get-menu', [MainController::class, 'getMenu']);
            $routes->any('user/auth/social/redirect/{provider}', [AuthController::class, 'redirect']);
            $routes->any('user/auth/social/callback/{provider}', [AuthController::class, 'callback']);
            $routes->any(
                'catalog/{search}',
                [CatalogController::class, 'index']
            )->where('search', '.*');
            $routes->any(
                'catalog',
                [CatalogController::class, 'index']
            )->where('search', '.*');
        });
    });
};
