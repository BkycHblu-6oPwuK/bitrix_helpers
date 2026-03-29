<?php

namespace Beeralex\Api\Domain\Basket;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $code
 * @property string $status
 * @property bool $isActive
 * 
 * DTO купона
 */
class CouponDTO extends Resource
{
    public static function make(array $coupon): static
    {
        return new static([
            'code' => $coupon['CODE'] ?? '',
            'status' => 'applied',
            'isActive' => !empty($coupon['CODE']),
        ]);
    }

    public static function empty(): static
    {
        return new static([
            'code' => '',
            'status' => 'none',
            'isActive' => false,
        ]);
    }
}
