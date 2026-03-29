<?php

use Bitrix\Main\Loader;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Service\PaginationService;
use Beeralex\Favorite\FavouriteService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexFavourite extends \CBitrixComponent
{
    protected FavouriteService $favouriteService;
    protected PaginationService $paginationService;
    protected CatalogService $catalogService;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['PAGE_SIZE'] = $arParams['PAGE_SIZE'] ?? 16;
        $arParams['IS_AVAILABLE'] = ($arParams['IS_AVAILABLE'] ?? 'Y') === 'Y';
        $arParams['APPLY_DISCOUNTS'] = ($arParams['APPLY_DISCOUNTS'] ?? 'Y') === 'Y';
        return $arParams;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('beeralex.favorite') || !Loader::includeModule('beeralex.catalog')) return;
        $this->favouriteService = service(FavouriteService::class);
        $this->paginationService = service(PaginationService::class);
        $this->catalogService = service(CatalogService::class);
        $this->arResult = $this->getTemplateData();
        $this->includeComponentTemplate();
    }

    private function getTemplateData(): array
    {
        $favoriteProductIds = $this->favouriteService->getByUser();
        $pagination = $this->paginationService->getPagination(count($favoriteProductIds), $this->arParams['PAGE_SIZE']);
        $favoriteProductIds = array_slice(
            $favoriteProductIds,
            $pagination['offset'],
            $this->arParams['PAGE_SIZE']
        );
        return [
            'PAGINATION' => $pagination,
            'ITEMS'      => $this->catalogService->getProductsWithOffers($favoriteProductIds, $this->arParams['IS_AVAILABLE'], $this->arParams['APPLY_DISCOUNTS']),
        ];
    }
}
