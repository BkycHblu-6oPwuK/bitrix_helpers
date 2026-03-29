<?php

namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\BasketBase;
use Beeralex\Catalog\Service\Basket\BasketFactory;

class BasketBuilder
{
    public function buildForBasket(BasketBase $basket): array
    {
        return \service(BasketFactory::class)->createBasketService($basket)->getBasketData();
    }
}
