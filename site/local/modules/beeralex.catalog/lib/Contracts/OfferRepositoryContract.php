<?php

namespace Beeralex\Catalog\Contracts;

use Beeralex\Core\Repository\CompiledEntityRepositoryContract;

interface OfferRepositoryContract extends CompiledEntityRepositoryContract
{
    /**
     * Получает ID торговых предложений, сгруппированные по ID товаров.
     */
    public function getOfferIdsByProductIds(array $productIds, bool $onlyAvailable = true): array;

    /**
     * Получает торговые предложения по их ID с ценами и остатками.
     */
    public function getOffersByIds(array $offerIds): array;

    /**
     * Получает предложения, сгруппированные по товарам.
     */
    public function getOffersByProductIds(array $productIds, bool $onlyAvailable = true): array;
}
