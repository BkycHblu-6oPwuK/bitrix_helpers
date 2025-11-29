<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $id
 * @property string $productId
 * @property string $extraId
 * @property string $catalogGroupId
 * @property float $price
 * @property string $currency
 * @property int $quantityFrom
 * @property int $quantityTo
 * @property float $priceScale
 * @property CatalogPriceGroupDTO|null $catalogGroup
 */
class CatalogPriceDTO extends Resource
{
    public static function make(array $catalogPrice): static
    {
        return new static([
            'id' => $catalogPrice['ID'] ?? '',
            'productId' => $catalogPrice['PRODUCT_ID'] ?? '',
            'extraId' => $catalogPrice['EXTRA_ID'] ?? '',
            'catalogGroupId' => $catalogPrice['CATALOG_GROUP_ID'] ?? '',
            'price' => $catalogPrice['PRICE'] ?? 0,
            'currency' => $catalogPrice['CURRENCY'] ?? '',
            'quantityFrom' => $catalogPrice['QUANTITY_FROM'] ?? 0,
            'quantityTo' => $catalogPrice['QUANTITY_TO'] ?? 0,
            'priceScale' => $catalogPrice['PRICE_SCALE'] ?? 0,
            'catalogGroup' => isset($catalogPrice['CATALOG_GROUP']) ? CatalogPriceGroupDTO::make($catalogPrice['CATALOG_GROUP']) : null,
        ]);
    }
}
