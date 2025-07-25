<?php

use Itb\Catalog\Products;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент слайдера
 */
class ItbProductSlider extends CBitrixComponent
{

    /** @inheritDoc */
    public function executeComponent()
    {
        if(!$this->arParams['IDS']) return;
        if ($this->startResultCache()) {
            $this->arResult['items'] = $this->getProducts($this->arParams['IDS']);
            $this->arResult['bigId'] = $this->arParams['BIG_ID'];
            $this->arResult['title'] = $this->arParams['TITLE'];
            $this->arResult['linkToAll'] = $this->arParams['LINK_TO_ALL'];
            $this->includeComponentTemplate();
        }
    }

    private function getProducts($productsIds)
    {
        if (is_array($productsIds) && !empty($productsIds)) {
            return Products::getProductsAndOffers($productsIds, true);
        } else {
            return [];
        }
    }
}
