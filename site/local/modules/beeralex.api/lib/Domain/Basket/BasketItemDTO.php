<?php

namespace Beeralex\Api\Domain\Basket;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property int $offerId
 * @property int $productId
 * @property bool $isOffer
 * @property int|float $quantity
 * @property float $price
 * @property string $priceFormatted
 * @property float $fullPrice
 * @property string $fullPriceFormatted
 * @property float|null $oldPrice
 * @property string|null $oldPriceFormatted
 * @property float|null $fullOldPrice
 * @property string|null $fullOldPriceFormatted
 * @property int|null $discountPercent
 * @property string $url
 * @property string $name
 * @property string $previewPictureSrc
 * @property string $detailPictureSrc
 * @property array $properties
 * 
 * DTO элемента корзины
 */
class BasketItemDTO extends Resource
{
    public static function make(array $basketItem): static
    {
        return new static([
            'id' => $basketItem['ID'] ?? 0,
            'code' => $basketItem['CODE'] ?? '',
            'offerId' => $basketItem['OFFER_ID'] ?? 0,
            'productId' => $basketItem['PRODUCT_ID'] ?? 0,
            'isOffer' => $basketItem['IS_OFFER'] ?? false,
            'quantity' => $basketItem['QUANTITY'] ?? 0,
            'price' => $basketItem['PRICE'] ?? 0.0,
            'priceFormatted' => $basketItem['PRICE_FORMATTED'] ?? '',
            'fullPrice' => $basketItem['FULL_PRICE'] ?? 0.0,
            'fullPriceFormatted' => $basketItem['FULL_PRICE_FORMATTED'] ?? '',
            'oldPrice' => $basketItem['OLD_PRICE'] ?? null,
            'oldPriceFormatted' => $basketItem['OLD_PRICE_FORMATTED'] ?? null,
            'fullOldPrice' => $basketItem['FULL_OLD_PRICE'] ?? null,
            'fullOldPriceFormatted' => $basketItem['FULL_OLD_PRICE_FORMATTED'] ?? null,
            'discountPercent' => $basketItem['DISCOUNT_PERCENT'] ?? null,
            'url' => $basketItem['URL'] ?? '',
            'name' => $basketItem['NAME'] ?? '',
            'previewPictureSrc' => $basketItem['PREVIEW_PICTURE_SRC'] ?? '',
            'detailPictureSrc' => $basketItem['DETAIL_PICTURE_SRC'] ?? '',
            'properties' => $basketItem['PROPERTIES'] ?? [],
        ]);
    }
}
