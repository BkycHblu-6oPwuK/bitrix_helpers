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
    public function getNewProductsIds(int $limit = 15, int $cacheTtl = 0, int $countMonts = 1): array;
    public function getPopularProductsIds(int $limit = 15, int $cacheTtl = 0): array;
    public function getSameProductsIds(int $elementId, int $sectionId, int $limit = 15, int $cacheTtl = 0): array;
    public function getAvailableProductIds(array $filter = []): array;
    public function getViewedProductsIds(int $currentElementId): array;
}
