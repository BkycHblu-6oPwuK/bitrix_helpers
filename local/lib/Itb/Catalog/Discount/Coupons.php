<?php

namespace Itb\Catalog\Discount;

use Bitrix\Main\Loader;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Registry;

Loader::includeModule('sale');

class Coupons
{
    /** @var DiscountCouponsManager|string $couponManager */
    public readonly string $couponManager;
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
        $this->couponManager::init();
        $this->couponManager::clear(true);
    }

    /**
     * @throws \Exception
     */
    public function applyCoupon(string $couponCode)
    {
        $this->clearCoupons();
        $coupon = $this->couponManager::getData($couponCode, true);
        $result = false;

        if ($coupon['ACTIVE'] == "Y") {
            $result = $this->couponManager::add($couponCode);
        }

        if (!$result) {
            throw new \Exception("Ошибка при применении купона");
        }

        return $result;
    }
}
