<?php
namespace Beeralex\Catalog\Helper;

use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Loader;
use CCurrencyLang;

class PriceHelper
{
    public static function getBasePriceId(): int
    {
        static $basePriceId = null;
        if ($basePriceId === null) {
            Loader::includeModule('catalog');
            $basePriceId = GroupTable::getBasePriceType()['ID'];
        }
        return $basePriceId;
    }

    public static function getDiscountPriceId(): int
    {
        static $discountPriceId = null;
        if ($discountPriceId === null) {
            Loader::includeModule('catalog');
            $discountPriceId = GroupTable::query()
                ->setSelect(['ID'])
                ->setFilter(['NAME' => 'АкцияИнтернетМагазин'])
                ->setLimit(1)
                ->setCacheTtl(86400)
                ->fetch()['ID'];
        }
        return $discountPriceId;
    }

    /**
     * Приводит цену к виду для отображения на сайте
     *
     * @param float $price
     *
     * @return string
     */
    public static function format(?float $price): string
    {
        Loader::includeModule('currency');
        return CCurrencyLang::CurrencyFormat($price, 'RUB', false);
    }

    /**
     * Получает процент скидки для цен
     *
     * @param float $oldPrice цена до скидки
     * @param float $newPrice цена со скидкой
     *
     * @return int процент скидки
     */
    public static function getSalePercent(?float $oldPrice, ?float $newPrice): int
    {
        if ($oldPrice < $newPrice) {
            return 0;
        }
        if ((float)$oldPrice == 0 || (float)$newPrice == 0) {
            return 0;
        } else {
            return round(($oldPrice - $newPrice) / $oldPrice * 100);
        }
    }

    public static function preparePrice(float $basePrice, float $discountPrice): array
    {
        $isDiscount = $discountPrice && $basePrice > $discountPrice;
        $price = $isDiscount ? $discountPrice : $basePrice;
        $oldPrice = $basePrice;

        $priceTypeId = static::getBasePriceId();
        if ($isDiscount) {
            $priceTypeId = static::getDiscountPriceId();
        }

        return [
            'priceValue' => $price,
            'priceFormatted' => static::format($price),
            'oldPriceValue' => $oldPrice,
            'oldPriceFormatted' => static::format($oldPrice),
            'discountPercent' => static::getSalePercent($oldPrice, $price),
            'priceTypeId' => $priceTypeId
        ];
    }

    public static function modifyPrice(array &$price, float $finalPrice): void
    {
        $price['priceValue'] = $finalPrice;
        $price['priceFormatted'] = static::format($finalPrice);
        $price['discountPercent'] = static::getSalePercent($price['oldPriceValue'], $finalPrice);
    }

    public static function getBaseCurrency()
    {
        static $currency = null;
        if ($currency === null) {
            Loader::includeModule('currency');
            $currency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
        }
        return $currency;
    }
}
