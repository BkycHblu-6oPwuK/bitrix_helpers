<?

declare(strict_types=1);

use Beeralex\Api\V1\Controllers\ArticlesController;
use Beeralex\Api\V1\Controllers\CatalogController;
use Beeralex\Api\V1\Controllers\FormController;
use Beeralex\Api\V1\Controllers\MainController;
use Beeralex\Api\V1\Controllers\ReviewController;
use Beeralex\Api\V1\Controllers\AuthController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->prefix('api')->group(function (RoutingConfigurator $routes) {
        $routes->prefix('v1')->group(function (RoutingConfigurator $routes) {
            $routes->prefix('user')->group(function (RoutingConfigurator $routes) {
                $routes->post('login', [AuthController::class, 'login']);
                $routes->post('login-fuser', [AuthController::class, 'loginFuser']);
                $routes->post('refresh', [AuthController::class, 'refresh']);
                $routes->post('register', [AuthController::class, 'register']);
                $routes->post('logout', [AuthController::class, 'logout']);
                $routes->get('methods', [AuthController::class, 'methods']);
            });

            $routes->get('get-content', [MainController::class, 'getContent']);
            $routes->get('get-menu', [MainController::class, 'getMenu']);

            $routes->get(
                'catalog/{search}',
                [CatalogController::class, 'index']
            )->where('search', '.*');
            $routes->get(
                'product/{search}',
                [CatalogController::class, 'index']
            )->where('search', '.*');
            $routes->get(
                'catalog',
                [CatalogController::class, 'index']
            );

            $routes->get(
                'articles',
                [ArticlesController::class, 'index']
            );
            $routes->get(
                'articles/{search}',
                [ArticlesController::class, 'index']
            )->where('search', '.*');

            $routes->get(
                'web-form/{formId}',
                [FormController::class, 'index']
            );
            $routes->post(
                'web-form/{formId}',
                [FormController::class, 'store']
            );

            $routes->get(
                'reviews',
                [ReviewController::class, 'index']
            );
            $routes->get(
                'reviews/{search}',
                [ReviewController::class, 'index']
            );
            $routes->post(
                'reviews/add',
                [ReviewController::class, 'store']
            );

            $routes->prefix('favorite')->group(function (RoutingConfigurator $routes) {
                $routes->post(
                    'add/{productID}',
                    [\Beeralex\Api\V1\Controllers\FavoriteController::class, 'store']
                );
                $routes->delete(
                    'delete/{productID}',
                    [\Beeralex\Api\V1\Controllers\FavoriteController::class, 'delete']
                );
                $routes->post(
                    'toggle/{productID}',
                    [\Beeralex\Api\V1\Controllers\FavoriteController::class, 'toggle']
                );
                $routes->delete(
                    'clear',
                    [\Beeralex\Api\V1\Controllers\FavoriteController::class, 'clear']
                );
                $routes->get(
                    'get',
                    [\Beeralex\Api\V1\Controllers\FavoriteController::class, 'get']
                );
            });
        });
    });
};
