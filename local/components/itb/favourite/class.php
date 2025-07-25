<?php

use Bitrix\Main\Loader;
use Itb\Catalog\Products;
use Itb\Core\Helpers\PaginationHelper;
use Itb\Favorite\Helper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @todo можно на vue перетащить, а получение избранного перенести на экшены и получать только по ajax
 */
class ItbFavourite extends \CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('itb.favorite')) return;
        $this->arResult= $this->getTemplateData();
        $this->includeComponentTemplate();
    }

    private function getTemplateData(): array
    {
        $favoriteProductIds = collect(Helper::getByUser());
        $pagination = PaginationHelper::getPagination($favoriteProductIds->count(), 16);
        $favoriteProductIds = $favoriteProductIds->forPage($pagination['currentPage'], $pagination['pageSize']);
        return [
            'pagination' => $pagination,
            'items'      => collect(Products::getProductsAndOffers($favoriteProductIds->toArray(), false))
                ->values()
                ->toArray()
        ];
    }
}
