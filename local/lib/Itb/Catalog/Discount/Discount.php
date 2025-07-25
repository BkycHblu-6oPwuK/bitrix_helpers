<?php

namespace Itb\Catalog\Discount;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Discount as SaleDiscount;
use Bitrix\Sale\Order;

Loader::includeModule('sale');
Loader::includeModule('currency');

class Discount
{
    protected static string $currency = '';
    protected static string $siteId = '';
    protected readonly ?Order $order;
    protected readonly BasketBase $basket;
    protected ?array $discounts = null;

    public function __construct(BasketBase $basket)
    {
        $this->initCurrency()->initSiteId();
        $this->basket = $basket;
        $this->order = $basket->getOrder();
    }

    protected function initCurrency()
    {
        if (!static::$currency) {
            static::$currency = CurrencyManager::getBaseCurrency();
        }
        return $this;
    }

    protected function initSiteId()
    {
        if (!static::$siteId) {
            static::$siteId = Context::getCurrent()->getSite();
        }
        return $this;
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
