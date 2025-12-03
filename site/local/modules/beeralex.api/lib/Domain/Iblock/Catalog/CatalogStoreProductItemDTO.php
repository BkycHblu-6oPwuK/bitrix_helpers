<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property int $storeId
 * @property int $productId
 * @property float $amount
 * @property float $quantityReserved
 * DTO для элемента остатка из таблицы b_catalog_store_product
 */
class CatalogStoreProductItemDTO extends Resource
{
    public static function make(array $item): static
    {
        return new static([
            'id' => (int)$item['ID'] ?? 0,
            'storeId' => (int)$item['STORE_ID'] ?? 0,
            'productId' => (int)$item['PRODUCT_ID'] ?? 0,
            'amount' => (float)$item['AMOUNT'] ?? 0.0,
            'quantityReserved' => (float)$item['QUANTITY_RESERVED'] ?? 0.0,
        ]);
    }
}
