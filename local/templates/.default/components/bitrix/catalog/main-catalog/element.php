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

use Itb\Catalog\Products;

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
    'itb:catalog.element',
    'vue_element',
    $componentElementParams
);


$APPLICATION->IncludeComponent(
    "bitrix:main.include",
    ".default",
    array(
        "AREA_FILE_SHOW" => "file",
        "AREA_FILE_SUFFIX" => "inc",
        "AREA_FILE_RECURSIVE" => "Y",
        "EDIT_TEMPLATE" => "standard.php",
        "COMPONENT_TEMPLATE" => ".default",
        "PATH" => "/include/main_page/tagline.php"
    ),
    false
);
if ($result) {
    $sameProductsIds = Products::getSameProductsIds((int)$result['elementId'], (int)$result['sectionId'], 86400);
    if (!empty($sameProductsIds)) {
        $APPLICATION->IncludeComponent(
            'itb:product_slider',
            'viewed',
            [
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => 86400,
                'IDS' => $sameProductsIds,
                'TITLE' => 'Может заинтересовать'
            ]
        );
    }
    $viewedIds = Products::getViewedProductsIds((int)$result['elementId']);
    if (!empty($viewedIds)) {
        $APPLICATION->IncludeComponent(
            'itb:product_slider',
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
