<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\Catalog\Service\Discount\CouponsService;
use Bitrix\Main\HttpRequest;

/**
 * Обработчик для сохранения купона из заголовка запроса
 */
class CouponHandler
{
    public static function handle(HttpRequest $request): void
    {
        if (!\Bitrix\Main\Loader::includeModule('sale') || !\Bitrix\Main\Loader::includeModule('beeralex.catalog')) {
            return;
        }

        try {
            $headers = $request->getHeaders();
            $coupon = $headers->get('X-Cart-Coupon');

            if (!$coupon) {
                return;
            }

            $couponService = service(CouponsService::class);

            $couponService->applyCoupon((string)$coupon);
        } catch (\Throwable $e) {
           
        }
    }
}
