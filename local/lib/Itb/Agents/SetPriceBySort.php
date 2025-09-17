<?php

namespace Itb\Agents;

use Bitrix\Main\Loader;
use Itb\Catalog\Price;
use Itb\Catalog\CatalogHelper;

class SetPriceBySort
{
    public static function setPriceBySort()
    {
        Loader::includeModule('catalog');
        $catalogIblockId = CatalogHelper::getCatalogIblockId();
        $basePriceId = Price::getBasePriceId();
        $salePriceId = Price::getDiscountPriceId();
        $newPrice = [];
        $oldPrice = [];
        $arSelect = ["ID", "CATALOG_PRICE_{$basePriceId}", "CATALOG_PRICE_{$salePriceId}","PROPERTY_PRICE_BY_SORT"];
        $arFilter = ["IBLOCK_ID" => $catalogIblockId,'ACTIVE' => 'Y'];
        $res = \CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
    
        while ($arFields = $res->Fetch()) {
            $price1 = $arFields["CATALOG_PRICE_{$basePriceId}"];
            $price2 = $arFields["CATALOG_PRICE_{$salePriceId}"];
            $priceSort = $price2 ? $price2 : $price1;
            $newPrice[$arFields['ID']] = round($priceSort);
            $oldPrice[$arFields['ID']] = $arFields['PROPERTY_PRICE_BY_SORT_VALUE'];
        }
    
        foreach($newPrice as $key => $item){
            if ($oldPrice[$key] != $item) {
                \CIBlockElement::SetPropertyValuesEx($key, $catalogIblockId, ["PRICE_BY_SORT" => $item]);
                \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($catalogIblockId, $key);
            }
        }
    
    }

    public static function exec()
    {
        static::setPriceBySort();
        return '\\' . __METHOD__ . '();';
    }
}
