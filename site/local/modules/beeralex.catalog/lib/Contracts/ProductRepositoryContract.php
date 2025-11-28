<?php

namespace Beeralex\Catalog\Contracts;

use Beeralex\Core\Repository\IblockRepositoryContract;

interface ProductRepositoryContract extends IblockRepositoryContract
{
    /**
     * Получает товары по ID со связкой цен.
     */
    public function getProducts(array $productIds): array;

    /**
     * Получает товар с его предложениями.
     */
    public function getProductWithOffers(int $productId): ?array;
}
