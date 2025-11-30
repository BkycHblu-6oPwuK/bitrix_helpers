<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $detailPageUrl
 * @property string $detailText
 * @property string $detailTextType
 * @property string $searchableContent
 * @property CatalogOfferDTO[] $offers
 * @property CatalogOfferDTO|null $preselectedOffer
 * @property CatalogPriceDTO[] $prices
 * @property CatalogProductDTO|null $catalog
 */
class CatalogItemDTO extends Resource
{
    public static function make(array $catalogItem): static
    {
        return new static([
            'id' => $catalogItem['ID'],
            'name' => $catalogItem['NAME'],
            'code' => $catalogItem['CODE'],
            'detailPageUrl' => $catalogItem['DETAIL_PAGE_URL'],
            'detailText' => $catalogItem['DETAIL_TEXT'],
            'detailTextType' => $catalogItem['DETAIL_TEXT_TYPE'],
            'searchableContent' => $catalogItem['SEARCHABLE_CONTENT'],
            'offers' => array_map(fn($offer) => CatalogOfferDTO::make($offer), $catalogItem['OFFERS'] ?? []),
            'preselectedOffer' => isset($catalogItem['PRESELECTED_OFFER'])
                ? CatalogOfferDTO::make($catalogItem['PRESELECTED_OFFER'])
                : null,
            'prices' => array_map([CatalogPriceDTO::class, 'make'], $catalogItem['PRICE'] ?? []),
            'catalog' => isset($catalogItem['CATALOG']) ? CatalogProductDTO::make($catalogItem['CATALOG']) : null,
        ]);
    }
}
