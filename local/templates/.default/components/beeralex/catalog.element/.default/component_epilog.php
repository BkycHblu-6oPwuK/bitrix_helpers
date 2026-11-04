<?php

use Beeralex\Api\ApiResult;
use Bitrix\Main\Loader;

service(ApiResult::class)->mergePageData($arResult['DTO']);

if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
    if (Bitrix\Main\Config\Option::get('catalog', 'enable_viewed_products') !== 'N') {
        $element = $arResult['DTO']?->element;
        if ($element) {
            Bitrix\Catalog\CatalogViewedProductTable::refresh(
                $element->preselectedOffer?->id,
                (int)\Bitrix\Sale\Fuser::getId(),
                $this->getSiteId(),
                $element->id
            );
        }
    }
}