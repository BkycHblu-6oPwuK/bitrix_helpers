<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Bitrix\Sale\BasketItemBase;

final class ShipmentLine
{
    /**
     * @param ?array{0:float,1:float,2:float} $dimensionsMm стороны в мм по убыванию
     */
    public function __construct(
        public readonly BasketItemBase $basketItem,
        public readonly float $quantity,
        public readonly float $price,
        public readonly float $weight,      // г, за единицу
        public readonly ?array $dimensionsMm,
    ) {}
}
