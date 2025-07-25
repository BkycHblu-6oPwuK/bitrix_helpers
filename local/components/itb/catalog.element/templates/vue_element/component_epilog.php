<?php

use Itb\Core\Assets\Vite;

Vite::getInstance()->includeAssets(['src/app/catalog_element/index.js']);

/** 
 * @var \CMain
 */
global $APPLICATION;
foreach ($arResult['seo']['sectionPath'] ?? [] as $section) {
    $APPLICATION->AddChainItem($section['title'], $section['url']);
}
