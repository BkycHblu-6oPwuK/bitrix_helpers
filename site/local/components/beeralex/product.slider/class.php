<?php

use Beeralex\Catalog\Service\CatalogService;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент слайдера
 */
class BeeralexProductSlider extends CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        if ($params['CATALOG_SERVICE'] === null || !($params['CATALOG_SERVICE'] instanceof CatalogService)) {
            Loader::requireModule('beeralex.catalog');
            $params['CATALOG_SERVICE'] = service(CatalogService::class);
        }

        return $params;
    }

    /** @inheritDoc */
    public function executeComponent()
    {
        if (!$this->arParams['IDS']) return;
        if ($this->startResultCache()) {
            $this->arResult['ITEMS'] = $this->getProducts($this->arParams['IDS']);
            $this->arResult['TITLE'] = $this->arParams['TITLE'];
            $this->arResult['LINK_TO_ALL'] = $this->arParams['LINK_TO_ALL'];
            $this->includeComponentTemplate();
        }
    }

    private function getProducts(array $productsIds)
    {
        if (!empty($productsIds)) {
            return $this->arParams['CATALOG_SERVICE']->getProductsWithOffers($productsIds, true);
        } else {
            return [];
        }
    }
}
