<?php

use Bitrix\Main\DI\ServiceLocator;
use App\Catalog\Type\Contracts\CatalogContextContract;
use App\Catalog\Type\Contracts\CatalogSwitcherContract;
use App\Catalog\Type\Enum\TypesCatalog;
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
        $swither = service(CatalogSwitcherContract::class);
        $current = $swither->get();
        $this->catalogContext = service(CatalogContextContract::class);
        $this->arResult['types'] = Config::getInstance()['SWITH_CATALOG_TYPES'] ? $this->catalogContext->getRootSections() : [];
        if(!empty($this->arResult['types']) && $current !== TypesCatalog::ALL) {
            LocalRedirect("/{$current->value}/");
        }
        $this->includeComponentTemplate();
        return $this->arResult;
    }

    protected function setSections() {}
}
