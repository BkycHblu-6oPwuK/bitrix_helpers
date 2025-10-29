<?

use App\Http\Controllers\Catalog\CatalogController;
use Beeralex\User\Controllers\AuthController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->prefix('api')->group(function (RoutingConfigurator $routes) {
        $routes->any('user/auth/social/redirect/{provider}', [AuthController::class, 'redirect']);
        $routes->any('user/auth/social/callback/{provider}', [AuthController::class, 'callback']);
        $routes->post(
            'catalog/{search}',
            [CatalogController::class, 'index']
        )->where('search', '.*');
        $routes->post(
            'catalog',
            [CatalogController::class, 'index']
        )->where('search', '.*');
    });
};
