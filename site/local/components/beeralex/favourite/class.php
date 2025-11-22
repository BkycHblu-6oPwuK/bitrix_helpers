<?php

use Bitrix\Main\Loader;
use Beeralex\Catalog\Helper\ProductsHelper;
use App\Favorite\Helper;
use Beeralex\Core\Service\PaginationService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @todo можно на vue перетащить, а получение избранного перенести на экшены и получать только по ajax
 */
class BeeralexFavourite extends \CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('beeralex.favorite')) return;
        $this->arResult= $this->getTemplateData();
        $this->includeComponentTemplate();
    }

    private function getTemplateData(): array
    {
        $favoriteProductIds = collect(Helper::getByUser());
        $pagination = service(PaginationService::class)->getPagination($favoriteProductIds->count(), 16);
        $favoriteProductIds = $favoriteProductIds->forPage($pagination['currentPage'], $pagination['pageSize']);
        return [
            'pagination' => $pagination,
            'items'      => collect(ProductsHelper::getProductsAndOffers($favoriteProductIds->toArray(), false))
                ->values()
                ->toArray()
        ];
    }
}
