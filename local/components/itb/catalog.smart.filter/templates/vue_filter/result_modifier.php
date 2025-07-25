<?php

use Bitrix\Iblock\SectionPropertyTable;
use Itb\Catalog\CatalogSection;

$items = [];
foreach($arResult['ITEMS'] as $key => &$item){
    if(!$item['PROPERTY_TYPE']) continue;
    if(empty($item['VALUES'])) continue;
    if($item['DISPLAY_TYPE'] == SectionPropertyTable::NUMBERS_WITH_SLIDER){
        $minPriceItem = &$item['VALUES']['MIN'];
        $maxPriceItem = &$item['VALUES']['MAX'];
        if(($minPriceItem['HTML_VALUE'] && $minPriceItem['VALUE'] !== $minPriceItem['HTML_VALUE']) || ($maxPriceItem['HTML_VALUE'] && $maxPriceItem['VALUE'] !== $maxPriceItem['HTML_VALUE'])){
            $minPriceItem['CHECKED'] = true;
            $maxPriceItem['CHECKED'] = true;
        }
    }
    $items[$key] = $item;
}
$arResult['VUE_DATA'] = [
    'filter_url' => $arResult['JS_FILTER_PARAMS']['SEF_SET_FILTER_URL'],
    'clear_url' => $arResult['JS_FILTER_PARAMS']['SEF_DEL_FILTER_URL'],
    'items' => $items,
    'sorting' => CatalogSection::getSorting(),
    'types' => [
        'checkbox' => SectionPropertyTable::CHECKBOXES,
        'range' => SectionPropertyTable::NUMBERS_WITH_SLIDER
    ]
];
$GLOBALS['vueFilterData'] = $arResult['VUE_DATA'];
$this->getComponent()->setResultCacheKeys(['VUE_DATA']);