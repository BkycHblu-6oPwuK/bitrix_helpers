<?php

use Bitrix\Main\DI\ServiceLocator;
use Beeralex\Catalog\Type\Contracts\CatalogContextContract;
use Beeralex\Catalog\Type\Contracts\CatalogSwitcherContract;
use Beeralex\Catalog\Type\Enum\TypesCatalog;
use Beeralex\Core\Config\Config;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexIndex extends CBitrixComponent
{
    protected CatalogContextContract $catalogContext;
    /** @inheritDoc */
    public function executeComponent()
    {
        $this->includeComponentTemplate();
        return $this->arResult;
    }

    protected function setSections() {}
}
