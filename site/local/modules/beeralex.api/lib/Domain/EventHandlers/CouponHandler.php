<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\Catalog\Service\Discount\CouponsService;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;

/**
 * Обработчик для применения купона из куки
 */
class CouponHandler
{
    public static function handle(HttpRequest $request): void
    {
        if (!Loader::includeModule('beeralex.catalog')) {
            return;
        }

        // Читаем купон из куки
        $coupon = trim((string)$request->getCookie('cart_coupon'));

        if ($coupon === '') {
            return;
        }

        $service = service(CouponsService::class);
        $current = $service->getApplyedCoupon();

        if ($current === $coupon) {
            return;
        }

        $service->applyCoupon($coupon);
    }
}
