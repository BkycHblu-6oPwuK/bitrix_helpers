<?php

use Itb\Helpers\PageHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент поиска
 */
class ItbSearch extends CBitrixComponent
{

    /** @inheritDoc */
    public function executeComponent()
    {
        $this->arResult['catalogUrl'] = PageHelper::getCatalogPageUrl();

        $this->includeComponentTemplate();
    }
}
