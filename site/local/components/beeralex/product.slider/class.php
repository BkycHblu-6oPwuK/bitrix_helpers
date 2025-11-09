<?php

use Beeralex\Catalog\Service\CatalogService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент слайдера
 */
class BeeralexProductSlider extends CBitrixComponent
{

    /** @inheritDoc */
    public function executeComponent()
    {
        if(!$this->arParams['IDS']) return;
        if ($this->startResultCache()) {
            $this->arResult['items'] = $this->getProducts($this->arParams['IDS']);
            $this->arResult['title'] = $this->arParams['TITLE'];
            $this->arResult['link'] = $this->arParams['LINK_TO_ALL'];
            $this->includeComponentTemplate();
        }
    }

    private function getProducts(array $productsIds)
    {
        if (!empty($productsIds)) {
            return service(CatalogService::class)->getProductsWithOffers($productsIds, true);
        } else {
            return [];
        }
    }
}
