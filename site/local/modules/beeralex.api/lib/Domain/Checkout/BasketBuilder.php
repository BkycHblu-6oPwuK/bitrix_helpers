<?php

namespace Beeralex\Catalog\Checkout;

use Bitrix\Sale\BasketBase;
use Beeralex\Catalog\Basket\BasketFacade;

class BasketBuilder
{
    public function buildForBasket(BasketBase $basket): array
    {
        return (new BasketFacade($basket))->getBasketData();
    }
}
