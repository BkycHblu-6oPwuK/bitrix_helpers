<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property int $offerId
 * @property int $productId
 * @property string $name
 * @property int $quantity
 * @property float $price
 * @property string $priceFormatted
 * @property float $oldPrice
 * @property string $oldPriceFormatted
 * @property float $fullPrice
 * @property string $fullPriceFormatted
 * @property float $fullOldPrice
 * @property string $fullOldPriceFormatted
 * @property int $discountPercent
 * @property bool $canBuy
 * @property string $url
 * @property string $pictureSrc
 * @property string $article
 * 
 * DTO товара в чекауте
 */
class CheckoutItemDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['ID'] ?? 0,
            'code' => $data['CODE'] ?? '',
            'offerId' => $data['OFFER_ID'] ?? 0,
            'productId' => $data['PRODUCT_ID'] ?? 0,
            'name' => $data['NAME'] ?? '',
            'quantity' => $data['QUANTITY'] ?? 0,
            'price' => $data['PRICE'] ?? 0.0,
            'priceFormatted' => $data['PRICE_FORMATTED'] ?? '',
            'oldPrice' => $data['OLD_PRICE'] ?? 0.0,
            'oldPriceFormatted' => $data['OLD_PRICE_FORMATTED'] ?? '',
            'fullPrice' => $data['FULL_PRICE'] ?? 0.0,
            'fullPriceFormatted' => $data['FULL_PRICE_FORMATTED'] ?? '',
            'fullOldPrice' => $data['FULL_OLD_PRICE'] ?? 0.0,
            'fullOldPriceFormatted' => $data['FULL_OLD_PRICE_FORMATTED'] ?? '',
            'discountPercent' => $data['DISCOUNT_PERCENT'] ?? 0,
            'canBuy' => $data['CAN_BUY'] ?? false,
            'url' => $data['DETAIL_PAGE_URL'] ?? '',
            'pictureSrc' => $data['PREVIEW_PICTURE_SRC']
                ?? $data['DETAIL_PICTURE_SRC']
                ?? (!empty($data['MORE_PHOTO']) && is_array($data['MORE_PHOTO']) ? $data['MORE_PHOTO'][0]['PICTURE_SRC']
                    ?? '' : ''),
            'article' => $data['CML2_ARTICLE']['VALUE'] ?? '',
        ]);
    }
}
