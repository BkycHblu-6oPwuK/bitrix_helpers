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
};
