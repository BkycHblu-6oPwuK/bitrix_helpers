<?php

namespace App\Catalog\Checkout;

use Bitrix\Sale\BasketBase;
use App\Catalog\Basket\BasketFacade;

class BasketBuilder
{
    public function buildForBasket(BasketBase $basket): array
    {
        return (new BasketFacade($basket))->getBasketData();
    }
}
