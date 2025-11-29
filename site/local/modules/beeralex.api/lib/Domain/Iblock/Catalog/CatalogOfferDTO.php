<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property bool $active
 * @property int $productId
 * @property CatalogProductDTO $catalog
 * @property CatalogPriceDTO[] $prices
 * @property CatalogStoreProductItemDTO[] $storeProduct
 * @property string $detailPageUrl
 */
class CatalogOfferDTO extends Resource
{
    public static function make(array $catalogOffer): static
    {
        return new static([
            'id' => (int)$catalogOffer['ID'] ?? 0,
            'active' => (bool)$catalogOffer['ACTIVE'] ?? false,
            'productId' => (int)$catalogOffer['CML2_LINK']['VALUE'] ?? 0,
            'catalog' => CatalogProductDTO::make($catalogOffer['CATALOG'] ?? []),
            'prices' => array_map([CatalogPriceDTO::class, 'make'], $catalogOffer['PRICE'] ?? []),
            'storeProduct' => array_map([CatalogStoreProductItemDTO::class, 'make'], $catalogOffer['STORE_PRODUCT'] ?? []),
            'detailPageUrl' => $catalogOffer['DETAIL_PAGE_URL'] ?? '',
        ]);
    }
}
