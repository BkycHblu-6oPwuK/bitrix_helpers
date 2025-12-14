<?php

namespace Beeralex\Api\Domain\Basket;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property BasketItemDTO[] $items
 * @property CouponDTO $coupon
 * @property BasketSummaryDTO $summary
 * 
 * DTO полных данных корзины
 */
class BasketDataDTO extends Resource
{
    public static function make(array $basketData): static
    {
        $items = array_map(
            [BasketItemDTO::class, 'make'],
            $basketData['ITEMS'] ?? []
        );

        $coupon = !empty($basketData['COUPON'])
            ? CouponDTO::make([
                'CODE' => $basketData['COUPON'],
            ])
            : CouponDTO::empty();

        $summary = BasketSummaryDTO::make($basketData['SUMMARY'] ?? []);

        return new static([
            'items' => $items,
            'coupon' => $coupon,
            'summary' => $summary,
        ]);
    }
}
