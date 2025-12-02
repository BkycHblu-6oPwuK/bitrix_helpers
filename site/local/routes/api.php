<?
declare(strict_types=1);

use Beeralex\Api\V1\Controllers\ArticlesController;
use Beeralex\Api\V1\Controllers\CatalogController;
use Beeralex\Api\V1\Controllers\FormController;
use Beeralex\Api\V1\Controllers\MainController;
use Beeralex\Api\V1\Controllers\User\AuthController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->prefix('api')->group(function (RoutingConfigurator $routes) {
        $routes->prefix('v1')->group(function (RoutingConfigurator $routes) {
            $routes->prefix('user')->group(function (RoutingConfigurator $routes) {
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
            
        });
    });
};
