<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property float $quantity
 * @property string $quantityTrace
 * @property float $weight
 * @property string|null $timestampX
 * @property string $priceType
 * @property int $recurSchemeLength
 * @property string $recurSchemeType
 * @property int $trialPriceId
 * @property bool $withoutOrder
 * @property bool $selectBestPrice
 * @property int $vatId
 * @property bool $vatIncluded
 * @property string $canBuyZero
 * @property string $negativeAmountTrace
 * @property string $tmpId
 * @property float $purchasingPrice
 * @property string $purchasingCurrency
 * @property bool $barcodeMulti
 * @property float $quantityReserved
 * @property string $subscribe
 * @property float $width
 * @property float $length
 * @property float $height
 * @property int $measure
 * @property string $type
 * @property bool $available
 * @property bool $bundle
 */
class CatalogProductDTO extends Resource
{
    public static function make(array $catalogProduct): static
    {
        return new static([
            'id' => $catalogProduct['ID'] ?? 0,
            'quantity' => $catalogProduct['QUANTITY'] ?? 0.0,
            'quantityTrace' => $catalogProduct['QUANTITY_TRACE'] ?? 'N',
            'weight' => $catalogProduct['WEIGHT'] ?? 0.0,
            'timestampX' => isset($catalogProduct['TIMESTAMP_X']) ? $catalogProduct['TIMESTAMP_X']->toString() : null,
            'priceType' => $catalogProduct['PRICE_TYPE'] ?? '',
            'recurSchemeLength' => $catalogProduct['RECUR_SCHEME_LENGTH'] ?? 0,
            'recurSchemeType' => $catalogProduct['RECUR_SCHEME_TYPE'] ?? '',
            'trialPriceId' => $catalogProduct['TRIAL_PRICE_ID'] ?? 0,
            'withoutOrder' => $catalogProduct['WITHOUT_ORDER'] ?? false,
            'selectBestPrice' => $catalogProduct['SELECT_BEST_PRICE'] ?? false,
            'vatId' => $catalogProduct['VAT_ID'] ?? 0,
            'vatIncluded' => $catalogProduct['VAT_INCLUDED'] ?? true,
            'canBuyZero' => $catalogProduct['CAN_BUY_ZERO'] ?? 'N',
            'negativeAmountTrace' => $catalogProduct['NEGATIVE_AMOUNT_TRACE'] ?? 'N',
            'tmpId' => $catalogProduct['TMP_ID'] ?? '',
            'purchasingPrice' => $catalogProduct['PURCHASING_PRICE'] ?? 0.0,
            'purchasingCurrency' => $catalogProduct['PURCHASING_CURRENCY'] ?? '',
            'barcodeMulti' => $catalogProduct['BARCODE_MULTI'] ?? false,
            'quantityReserved' => $catalogProduct['QUANTITY_RESERVED'] ?? 0.0,
            'subscribe' => $catalogProduct['SUBSCRIBE'] ?? 'Y',
            'width' => $catalogProduct['WIDTH'] ?? 0.0,
            'length' => $catalogProduct['LENGTH'] ?? 0.0,
            'height' => $catalogProduct['HEIGHT'] ?? 0.0,
            'measure' => $catalogProduct['MEASURE'] ?? 0,
            'type' => $catalogProduct['TYPE'] ?? '',
            'available' => $catalogProduct['AVAILABLE'] ?? false,
            'bundle' => $catalogProduct['BUNDLE'] ?? false,
        ]);
    }
}
