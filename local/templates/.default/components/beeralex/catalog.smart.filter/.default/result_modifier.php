<?php

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\FilterDTO;
use Beeralex\Core\Service\UrlService;
use Bitrix\Iblock\SectionPropertyTable;

/**
 * @var BeeralexCatalogSmartFilter $component
 */
$component = $this->getComponent();
$urlService = service(UrlService::class);

if ($arParams['USE_FILTER']) {
    $items = [];
    foreach ($arResult['ITEMS'] as $key => &$item) {
        if (!$item['PROPERTY_TYPE']) continue;
        if (empty($item['VALUES'])) continue;
        if ($item['DISPLAY_TYPE'] == SectionPropertyTable::NUMBERS_WITH_SLIDER || (isset($item['VALUES']['MIN']) && isset($item['VALUES']['MAX']))) {
            $minPriceItem = &$item['VALUES']['MIN'];
            $maxPriceItem = &$item['VALUES']['MAX'];
            if (((float)$minPriceItem['HTML_VALUE'] && (float)$minPriceItem['VALUE'] !== (float)$minPriceItem['HTML_VALUE']) || ((float)$maxPriceItem['HTML_VALUE'] && (float)$maxPriceItem['VALUE'] !== (float)$maxPriceItem['HTML_VALUE'])) {
                $minPriceItem['CHECKED'] = true;
                $maxPriceItem['CHECKED'] = true;
            }
        }
        $items[$key] = $item;
    }

    $arResult['DTO'] = FilterDTO::make([
        'filterUrl' => $urlService->cleanUrl($arResult['JS_FILTER_PARAMS']['SEF_SET_FILTER_URL']),
        'clearUrl' => $urlService->cleanUrl($arResult['JS_FILTER_PARAMS']['SEF_DEL_FILTER_URL']),
        'sectionUrl' => $urlService->cleanUrl($arParams['SECTION_URL'] ?? ''),
        'items' => $items,
        'sorting' => $component->getSortingService()?->getSorting(),
        'types' => [
            'checkbox' => SectionPropertyTable::CHECKBOXES,
            'range' => SectionPropertyTable::NUMBERS_WITH_SLIDER,
            'numbers' => SectionPropertyTable::NUMBERS,
            'dropdown' => SectionPropertyTable::DROPDOWN,
            'calendar' => SectionPropertyTable::CALENDAR,
            'radio' => SectionPropertyTable::RADIO_BUTTONS,
        ]
    ]);
} else {
    $arResult['DTO'] = FilterDTO::make([
        'sectionUrl' => $urlService->cleanUrl($arParams['SECTION_URL'] ?? ''),
        'sorting' => $component->getSortingService()?->getSorting(),
    ]);
}

service(ApiResult::class)->addPageData($arResult['DTO'], 'filter');
$component->setResultCacheKeys(['DTO']);
