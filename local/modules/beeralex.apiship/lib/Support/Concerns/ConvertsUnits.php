<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support\Concerns;

trait ConvertsUnits
{
    protected static function mmToCm(float $mm): int
    {
        return max(1, (int)ceil($mm / 10));
    }

    /** Ставка Bitrix (доля, 0.2) -> процент ApiShip; null -> -1 (без НДС). */
    protected static function vatToPercent(?float $rate): int
    {
        return $rate === null ? -1 : (int)round($rate * 100);
    }
}