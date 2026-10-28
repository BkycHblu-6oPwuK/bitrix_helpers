<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\AbstractIblockItemDTO;
use Beeralex\Api\Domain\Iblock\PropertyItemDTO;

/** 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $detailPageUrl
 * @property string $detailText
 * @property string $detailTextType
 * @property string $previewText
 * @property string $previewTextType
 * @property string $searchableContent
 * @property CatalogOfferDTO[]|null $offers
 * @property CatalogOfferDTO|null $preselectedOffer
 * @property CatalogPriceDTO[]|null $prices
 * @property CatalogProductDTO|null $catalog
 * @property OffersTreeDTO|null $offersTree
 * @property PropertyItemDTO[] $properties
 * DTO элемента товара
 */
class CatalogItemDTO extends AbstractIblockItemDTO
{
    /**
     * Поместит свойства если в выборке свойств был IBLOCK_PROPERTY_ID
     */
    public static function make(array $catalogItem): static
    {
        $properties = static::getFromDecomposeProperties($catalogItem);
        return new static([
            'id' => $catalogItem['ID'] ?? 0,
            'name' => $catalogItem['NAME'] ?? '',
            'code' => $catalogItem['CODE'] ?? '',
            'detailPageUrl' => $catalogItem['DETAIL_PAGE_URL'] ?? '',
            'previewText' => $catalogItem['PREVIEW_TEXT'] ?? '',
            'previewTextType' => $catalogItem['PREVIEW_TEXT_TYPE'] ?? '',
            'detailText' => $catalogItem['DETAIL_TEXT'] ?? '',
            'previewPictureSrc' => $catalogItem['PREVIEW_PICTURE_SRC'] ?? '',
            'detailPictureSrc' => $catalogItem['DETAIL_PICTURE_SRC'] ?? '',
            'detailTextType' => $catalogItem['DETAIL_TEXT_TYPE'] ?? '',
            'searchableContent' => $catalogItem['SEARCHABLE_CONTENT'] ?? '',
            'offers' => !empty($catalogItem['OFFERS']) ? array_map(fn($offer) => CatalogOfferDTO::make($offer), $catalogItem['OFFERS']) : null,
            'preselectedOffer' => isset($catalogItem['PRESELECTED_OFFER'])
                ? CatalogOfferDTO::make($catalogItem['PRESELECTED_OFFER'])
                : null,
            'prices' => !empty($catalogItem['PRICE']) ? array_map([CatalogPriceDTO::class, 'make'], $catalogItem['PRICE']) : null,
            'catalog' => isset($catalogItem['CATALOG']) && is_array($catalogItem['CATALOG']) && count($catalogItem['CATALOG']) > 1 ? CatalogProductDTO::make($catalogItem['CATALOG']) : null, // если 1, то вероятно только ID товара без дополнительных данных - DTO не нужна
            'properties' => $properties,
            'offersTree' => isset($catalogItem['OFFER_TREE']) ? OffersTreeDTO::make($catalogItem['OFFER_TREE']) : null,
        ]);
    }
}
