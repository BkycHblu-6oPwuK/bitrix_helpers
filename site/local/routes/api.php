<?

declare(strict_types=1);

use Beeralex\Api\V1\Controllers\ArticlesController;
use Beeralex\Api\V1\Controllers\CatalogController;
use Beeralex\Api\V1\Controllers\FormController;
use Beeralex\Api\V1\Controllers\MainController;
use Beeralex\Api\V1\Controllers\ReviewController;
use Beeralex\Api\V1\Controllers\UserController;
use Beeralex\Api\V1\Controllers\AuthController;
use Beeralex\Api\V1\Controllers\FavoriteController;
use Beeralex\Api\V1\Controllers\BasketController;
use Beeralex\Api\V1\Controllers\CheckoutController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->prefix('api')->group(function (RoutingConfigurator $routes) {
        $routes->prefix('v1')->group(function (RoutingConfigurator $routes) {
            $routes->prefix('user')->group(function (RoutingConfigurator $routes) {
                $routes->get('me', [UserController::class, 'me']);
                $routes->post('login', [AuthController::class, 'login']);
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
                    [FavoriteController::class, 'store']
                );
                $routes->delete(
                    'delete/{productID}',
                    [FavoriteController::class, 'delete']
                );
                $routes->post(
                    'toggle/{productID}',
                    [FavoriteController::class, 'toggle']
                );
                $routes->delete(
                    'clear',
                    [FavoriteController::class, 'clear']
                );
                $routes->get(
                    'get',
                    [FavoriteController::class, 'get']
                );
                $routes->get(
                    'page',
                    [FavoriteController::class, 'page']
                );
            });

            $routes->prefix('basket')->group(function (RoutingConfigurator $routes) {
                $routes->get(
                    'get-ids',
                    [BasketController::class, 'getIds']
                );
                $routes->get(
                    'get',
                    [BasketController::class, 'get']
                );
                $routes->post(
                    'add/{offerId}',
                    [BasketController::class, 'add']
                );
                $routes->post(
                    'update/{offerId}',
                    [BasketController::class, 'update']
                );
                $routes->delete(
                    'delete/{offerId}',
                    [BasketController::class, 'delete']
                );
                $routes->delete(
                    'clear',
                    [BasketController::class, 'clear']
                );
                $routes->post(
                    'apply-coupon',
                    [BasketController::class, 'applyCoupon']
                );
            });

            $routes->prefix('checkout')->group(function (RoutingConfigurator $routes) {
                $routes->get(
                    'get',
                    [CheckoutController::class, 'get']
                );
                $routes->post(
                    'refresh',
                    [CheckoutController::class, 'refresh']
                );
                $routes->post(
                    'create',
                    [CheckoutController::class, 'store']
                );
            });
        });
    });
};
