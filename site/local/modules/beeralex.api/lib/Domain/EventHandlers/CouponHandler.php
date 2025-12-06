<?

namespace Beeralex\Api\Domain\EventHandlers;

use Beeralex\Catalog\Service\Discount\CouponsService;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;

/**
 * Обработчик для сохранения купона из заголовка запроса
 */
class CouponHandler
{
    public static function handle(HttpRequest $request): void
    {
        if (!Loader::includeModule('beeralex.catalog')) {
            return;
        }

        $headers = $request->getHeaders();
        $coupon = trim((string)$headers->get('X-Cart-Coupon'));

        if ($coupon === '') {
            return;
        }

        $service = service(CouponsService::class);
        $current = $service->getApplyedCoupon();

        if ($current === $coupon) {
            return;
        }

        $result = $service->applyCoupon($coupon);
        $response = Context::getCurrent()->getResponse();

        if ($result->isSuccess()) {
            $response->addHeader('X-Coupon-Status', 'applied');
        } else {
            $response->addHeader('X-Coupon-Status', 'error');
        }
    }
}
