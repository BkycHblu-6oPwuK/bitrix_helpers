<?php

use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Service\IblockService;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.section");

class BeeralexCatalogSection extends \CatalogSectionComponent
{
    public function onPrepareComponentParams($params)
    {
        if (!$params['IBLOCK_ID']) {
            $params['IBLOCK_ID'] = service(IblockService::class)->getIblockIdByCode('catalog');
        }

        if($params['CATALOG_SERVICE'] === null || !($params['CATALOG_SERVICE'] instanceof CatalogService)) {
            Loader::requireModule('beeralex.catalog');
            $params['CATALOG_SERVICE'] = service(CatalogService::class);
        }

        return parent::onPrepareComponentParams($params);
    }
}