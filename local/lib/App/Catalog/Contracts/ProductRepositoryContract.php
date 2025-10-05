<?php

namespace App\Catalog\Contracts;

use Itb\Core\Repository\CompiledEntityRepositoryContract;

interface ProductRepositoryContract extends CompiledEntityRepositoryContract
{
    /**
     * Получает товары по ID со связкой цен.
     */
    public function getProducts(array $productIds): array;
}
