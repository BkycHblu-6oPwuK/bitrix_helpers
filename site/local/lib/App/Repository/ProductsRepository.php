<?php

declare(strict_types=1);

namespace App\Repository;

use Beeralex\Catalog\Repository\ProductsRepository as CatalogProductsRepository;

class ProductsRepository extends CatalogProductsRepository
{
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        if (empty($productIds)) {
            return [];
        }

        $filter = ['ID' => $productIds];
        if ($onlyActive) {
            $filter['ACTIVE'] = 'Y';
        }

        $items = $this->findAll(
            $filter,
            ['*', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CATALOG', 'BRAND_REF']
        );

        $products = [];
        foreach ($items as $item) {
            $products[(int)$item['ID']] = $item;
        }

        $result = [];
        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $result[] = $products[$id];
            }
        }

        return $result;
    }
}
