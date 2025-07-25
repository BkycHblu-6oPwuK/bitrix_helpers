<?php

namespace Itb\Checkout;

use Bitrix\Sale\BasketBase;
use Itb\Catalog\BasketFacade;

class BasketBuilder
{
    public function buildForBasket(BasketBase $basket): array
    {
        return (new BasketFacade($basket))->getBasketData();
    }
}
