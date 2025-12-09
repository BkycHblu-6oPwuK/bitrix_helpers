<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Service\Discount;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Discount as SaleDiscount;
use Bitrix\Sale\Order;

class DiscountService
{
    protected ?Order $order = null;
    protected ?array $discounts = null;

    public function __construct(
        protected readonly BasketBase $basket
    )
    {
        $this->order = $basket->getOrder();
    }

    protected function getSiteId()
    {
        return $this->order?->getSiteId() ?? Context::getCurrent()->getSite() ?? 's1';
    }

    public function getPrice(int|string $basketCode): ?float
    {
        $discounts = $this->getDiscounts();
        $price = null;
        if (isset($discounts["PRICES"]['BASKET'][$basketCode])) {
            $price = (float)$discounts["PRICES"]['BASKET'][$basketCode]["PRICE"];
        }
        return $price;
    }

    public function getDiscounts(): array
    {
        if ($this->discounts === null) {
            $discounts = $this->getSaleDiscounts();
            $discounts->calculate();
            $this->discounts = $discounts->getApplyResult(true);
        }
        return $this->discounts;
    }

    protected function getSaleDiscounts(): SaleDiscount
    {
        if ($this->order) {
            return SaleDiscount::buildFromOrder($this->order);
        }
        return SaleDiscount::buildFromBasket($this->basket, new \Bitrix\Sale\Discount\Context\Fuser($this->basket->getFUserId(true)));
    }
}
