<?php

namespace Beeralex\Api\Domain\Basket;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $totalQuantity
 * @property float $totalPrice
 * @property string $totalPriceFormatted
 * @property float $totalDiscount
 * @property string $totalDiscountFormatted
 * 
 * DTO итоговых данных корзины
 */
class BasketSummaryDTO extends Resource
{
    public static function make(array $summary): static
    {
        return new static([
            'totalQuantity' => $summary['TOTAL_QUANTITY'] ?? 0,
            'totalPrice' => $summary['TOTAL_PRICE'] ?? 0.0,
            'totalPriceFormatted' => $summary['TOTAL_PRICE_FORMATTED'] ?? '',
            'totalDiscount' => $summary['TOTAL_DISCOUNT'] ?? 0.0,
            'totalDiscountFormatted' => $summary['TOTAL_DISCOUNT_FORMATTED'] ?? '',
        ]);
    }
}
