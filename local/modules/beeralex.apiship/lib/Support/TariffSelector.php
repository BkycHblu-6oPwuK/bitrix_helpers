<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Dto\TariffDto;

final class TariffSelector
{
    /**
     * Самый дешёвый тариф среди разрешённых; при равной цене — с меньшим сроком.
     *
     * @param TariffDto[] $tariffs
     * @param int[] $allowedIds пусто — любые
     */
    public function pickCheapest(array $tariffs, array $allowedIds = []): ?TariffDto
    {
        $best = null;

        foreach ($tariffs as $tariff) {
            if ($allowedIds !== [] && !in_array((int)$tariff->tariffId, $allowedIds, true)) {
                continue;
            }
            if ($best === null || $this->isBetter($tariff, $best)) {
                $best = $tariff;
            }
        }

        return $best;
    }

    private function isBetter(TariffDto $candidate, TariffDto $current): bool
    {
        if ($candidate->deliveryCost !== $current->deliveryCost) {
            return $candidate->deliveryCost < $current->deliveryCost;
        }
        return $candidate->daysMin < $current->daysMin;
    }
}
