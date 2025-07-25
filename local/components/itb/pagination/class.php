<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * работает на основе массива pagination из arParams, массив формировать через PaginationHelper
 */
class ItbPagination extends \CBitrixComponent
{
    public function executeComponent()
    {
        if(!$this->arParams['PAGINATION']) return;
        global $APPLICATION;
        $this->arResult['BASE_URL'] = $APPLICATION->GetCurPage() . "?{$this->arParams['PAGINATION']['paginationUrlParam']}=" ;
        $this->includeComponentTemplate();
    }
}
