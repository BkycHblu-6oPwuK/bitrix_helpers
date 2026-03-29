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
    protected readonly CatalogService $catalogService;
    
    public function onPrepareComponentParams($params)
    {
        Loader::requireModule('beeralex.catalog');
        $this->catalogService = service(CatalogService::class);

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
            return $this->catalogService->getProductsWithOffers($productsIds, true);
        } else {
            return [];
        }
    }
}
