<?php
namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $value
 * @property bool $isVerified
 * @property float $discount
 * @property string $discountFormatted
 * 
 * DTO купона
 */
class CouponDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'value' => $data['value'] ?? '',
            'isVerified' => $data['isVerified'] ?? false,
            'discount' => $data['discount'] ?? 0.0,
            'discountFormatted' => $data['discountFormatted'] ?? '',
        ]);
    }
}
