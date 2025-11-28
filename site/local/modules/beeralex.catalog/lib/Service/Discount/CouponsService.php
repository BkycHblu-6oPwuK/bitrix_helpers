<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Service\Discount;

use Bitrix\Main\Result;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Registry;

class CouponsService
{
    /** @var DiscountCouponsManager|string $couponManager */
    protected readonly string $couponManager;

    public function __construct()
    {
        $registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
        $this->couponManager = $registry->getDiscountCouponClassName();
    }

    public function getApplyedCoupon(): string
    {
        return $this->couponManager::get(false, ['STATUS' => $this->couponManager::STATUS_APPLYED])[0] ?: '';
    }

    public function clearCoupons(): void
    {
        $this->couponManager->init();
        $this->couponManager->clear(true);
    }

    /**
     * @throws \Exception
     */
    public function applyCoupon(string $couponCode): Result
    {
        $result = new Result();
        $this->clearCoupons();
        $coupon = $this->couponManager->getData($couponCode, true);
        $resultApply = false;

        if ($coupon['ACTIVE'] == "Y") {
            $resultApply = $this->couponManager->add($couponCode);
        }

        if (!$resultApply) {
            $result->addError(new \Bitrix\Main\Error("Ошибка при применении купона", 'coupon'));
            return $result;
        }

        return $result;
    }
}
