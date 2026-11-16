<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support\Concerns;

trait ConvertsUnits
{
    protected static function mmToCm(float $mm): int
    {
        return max(1, (int)ceil($mm / 10));
    }

    /**
     * Ставка Bitrix (доля, 0.2) -> процент ApiShip. null и ставки вне набора, который принимает API
     * (например, устаревшие 18%), -> -1 (без НДС).
     */
    protected static function vatToPercent(?float $rate): int
    {
        if ($rate === null) {
            return -1;
        }

        $percent = (int)round($rate * 100);

        return in_array($percent, static::apiVatRates(), true) ? $percent : -1;
    }

    /** @return int[] допустимые значения costVat / deliveryCostVat из OpenAPI */
    protected static function apiVatRates(): array
    {
        return [0, 5, 7, 10, 20, 22];
    }
}
