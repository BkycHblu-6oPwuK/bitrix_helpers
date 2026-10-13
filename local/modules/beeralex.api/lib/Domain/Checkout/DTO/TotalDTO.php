<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $basket
 * @property string $basketFormatted
 * @property float $basketWithoutDiscount
 * @property string $basketWithoutDiscountFormatted
 * @property float $delivery
 * @property string $deliveryFormatted
 * @property float $discount
 * @property string $discountFormatted
 * @property float $total
 * @property string $totalFormatted
 * @property int $quantity
 * @property int $positionQuantity
 * 
 * DTO итоговой цены заказа
 */
class TotalDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'basket' => $data['basket'] ?? 0,
            'basketFormatted' => $data['basketFormatted'] ?? '',
            'basketWithoutDiscount' => $data['basketWithoutDiscount'] ?? 0,
            'basketWithoutDiscountFormatted' => $data['basketWithoutDiscountFormatted'] ?? '',
            'delivery' => $data['delivery'] ?? 0.0,
            'deliveryFormatted' => $data['deliveryFormatted'] ?? '',
            'discount' => $data['discount'] ?? 0.0,
            'discountFormatted' => $data['discountFormatted'] ?? '',
            'total' => $data['total'] ?? 0.0,
            'totalFormatted' => $data['totalFormatted'] ?? '',
            'quantity' => $data['quantity'] ?? 0,
            'positionQuantity' => $data['positionQuantity'] ?? 0,
        ]);
    }
}
