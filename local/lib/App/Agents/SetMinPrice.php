<?php

namespace App\Agents;

use Bitrix\Main\Loader;
use App\Catalog\Helper\PriceHelper;
use Itb\Core\Helpers\IblockHelper;

class SetMinPrice
{
    public static function setMinPrice()
    {
        Loader::includeModule('iblock');
        $catalogIblockId = IblockHelper::getIblockIdByCode('catalog');
        $basePriceId = PriceHelper::getBasePriceId();
        $salePriceId = PriceHelper::getDiscountPriceId();
        $newPrice = [];
        $oldPrice = [];
        $arSelect = ["ID", "CATALOG_PRICE_{$basePriceId}", "CATALOG_PRICE_{$salePriceId}", "PROPERTY_MIN_PRICE"];
        $arFilter = ["IBLOCK_ID" => $catalogIblockId, "ACTIVE" => "Y"];
        $res = \CIBlockElement::GetList([], $arFilter, false, [], $arSelect);

        while ($arFields = $res->Fetch()) {
            $price = !empty($arFields["CATALOG_PRICE_{$salePriceId}"]) ? $arFields["CATALOG_PRICE_{$salePriceId}"] : $arFields["CATALOG_PRICE_{$basePriceId}"];
            $newPrice[$arFields['ID']] = round($price);
            $oldPrice[$arFields['ID']] = $arFields['PROPERTY_MIN_PRICE_VALUE'];
        }
        
        foreach($newPrice as $key => $item){
            if ($oldPrice[$key] != $item) {
                \CIBlockElement::SetPropertyValuesEx($key, $catalogIblockId, ['MIN_PRICE' => $item]);
                \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($catalogIblockId, $key);
            }
        }
    }

    public static function exec()
    {
        static::setMinPrice();
        return '\\' . __METHOD__ . '();';
    }
}
