<?php

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Enum\DIServiceKey;
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
    protected readonly ProductRepositoryContract $productRepository;
    
    public function onPrepareComponentParams($params)
    {
        Loader::requireModule('beeralex.catalog');
        $this->catalogService = service(CatalogService::class);
        $this->productRepository = service(DIServiceKey::PRODUCT_REPOSITORY->value);
        if (!isset($params['COUNT']) || $params['COUNT'] <= 0) {
            $params['COUNT'] = 40;
        }

        return $params;
    }

    /** @inheritDoc */
    public function executeComponent()
    {
        if (!$this->arParams['IDS']) return;
        if ($this->startResultCache()) {
            if($this->arParams['COUNT'] > 0 && count($this->arParams['IDS']) > $this->arParams['COUNT']) {
                $availableIds = $this->productRepository->getAvailableProductIds(['ID' => $this->arParams['IDS']]);
                $this->arParams['IDS'] = array_slice($availableIds, 0, $this->arParams['COUNT']);
            }

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
