<?
use Beeralex\User\Controllers\AuthController;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->any('/user/auth/social/redirect/{provider}', [AuthController::class, 'redirect']);
    $routes->any('/user/auth/social/callback/{provider}', [AuthController::class, 'callback']);
};
