<?php

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\FilterDTO;
use Bitrix\Iblock\SectionPropertyTable;
use Beeralex\Catalog\Service\SortingService;

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

$urlService = service(\Beeralex\Core\Service\UrlService::class);

$arResult['DTO'] = FilterDTO::make([
    'filterUrl' => $urlService->cleanUrl($arResult['JS_FILTER_PARAMS']['SEF_SET_FILTER_URL']),
    'clearUrl' => $urlService->cleanUrl($arResult['JS_FILTER_PARAMS']['SEF_DEL_FILTER_URL']),
    'items' => $items,
    'sorting' => service(SortingService::class)->getSorting(),
    'types' => [
        'checkbox' => SectionPropertyTable::CHECKBOXES,
        'range' => SectionPropertyTable::NUMBERS_WITH_SLIDER,
        'numbers' => SectionPropertyTable::NUMBERS,
        'dropdown' => SectionPropertyTable::DROPDOWN,
        'calendar' => SectionPropertyTable::CALENDAR,
        'radio' => SectionPropertyTable::RADIO_BUTTONS,
    ]
]);
service(ApiResult::class)->addPageData($arResult['DTO'], 'filter');
$this->getComponent()->setResultCacheKeys(['DTO']);