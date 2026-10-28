<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\AbstractIblockItemDTO;
use Beeralex\Api\Domain\Iblock\PropertyItemDTO;

/** 
 * @property int $id
 * @property bool $active
 * @property int $productId
 * @property CatalogProductDTO|null $catalog
 * @property CatalogPriceDTO[]|null $prices
 * @property CatalogStoreProductItemDTO[]|null $storeProduct
 * @property string $detailPageUrl
 * @property PropertyItemDTO[] $properties
 * DTO элемента предложения
 */
class CatalogOfferDTO extends AbstractIblockItemDTO
{
    public static function make(array $catalogOffer): static
    {
        $properties = static::getFromDecomposeProperties($catalogOffer);
        return new static([
            'id' => (int)$catalogOffer['ID'] ?? 0,
            'active' => (bool)$catalogOffer['ACTIVE'] ?? false,
            'productId' => (int)$catalogOffer['CML2_LINK']['VALUE'] ?? 0,
            'catalog' => isset($catalogOffer['CATALOG']) ? CatalogProductDTO::make($catalogOffer['CATALOG']) : null,
            'prices' => isset($catalogOffer['PRICE']) ? array_map([CatalogPriceDTO::class, 'make'], $catalogOffer['PRICE']) : [],
            'storeProduct' => isset($catalogOffer['STORE_PRODUCT']) ? array_map([CatalogStoreProductItemDTO::class, 'make'], $catalogOffer['STORE_PRODUCT']) : [],
            'detailPageUrl' => $catalogOffer['DETAIL_PAGE_URL'] ?? '',
            'properties' => $properties,
        ]);
    }
}
