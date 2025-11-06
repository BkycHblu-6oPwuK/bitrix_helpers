<?php

/**
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var string $componentPath
 * @var CBitrixComponent $component
 */

use Beeralex\Catalog\Helper\ProductsHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$componentElementParams = array(
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
    'CACHE_TIME' => $arParams['CACHE_TIME'],
    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
    'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
    'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'],
    'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
    'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'],
    'SECTION_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['section'],
    'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
);
/**
 * @var ?array $result
 */
$result = $APPLICATION->IncludeComponent(
    'beeralex:catalog.element',
    'vue_element',
    $componentElementParams
);

if ($result) {
    $sameProductsIds = ProductsHelper::getSameProductsIds((int)$result['elementId'], (int)$result['sectionId'], 86400);
    if (!empty($sameProductsIds)) {
        $APPLICATION->IncludeComponent(
            'beeralex:product.slider',
            'viewed',
            [
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => 86400,
                'IDS' => $sameProductsIds,
                'TITLE' => 'Может заинтересовать'
            ]
        );
    }
    $viewedIds = ProductsHelper::getViewedProductsIds((int)$result['elementId']);
    if (!empty($viewedIds)) {
        $APPLICATION->IncludeComponent(
            'beeralex:product.slider',
            'viewed',
            [
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => 86400,
                'IDS' => $viewedIds,
                'TITLE' => 'Вы недавно смотрели'
            ]
        );
    }
}
