<?php

use Bitrix\Main\DI\ServiceLocator;
use Itb\Catalog\Types\Contracts\CatalogContextContract;
use Itb\Catalog\Types\Contracts\CatalogSwitcherContract;
use Itb\Catalog\Types\Enum\TypesCatalog;
use Itb\Core\Config;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ItbIndex extends CBitrixComponent
{
    protected CatalogContextContract $catalogContext;
    /** @inheritDoc */
    public function executeComponent()
    {
        /**
         * @var CatalogSwitcherContract $swither
         */
        $swither = ServiceLocator::getInstance()->get(CatalogSwitcherContract::class);
        $current = $swither->get();
        $this->catalogContext = ServiceLocator::getInstance()->get(CatalogContextContract::class);
        $this->arResult['types'] = Config::getInstance()->enableSwitchCatalogType ? $this->catalogContext->getRootSections() : [];
        if(!empty($this->arResult['types']) && $current !== TypesCatalog::ALL) {
            LocalRedirect("/{$current->value}/");
        }
        $this->includeComponentTemplate();
        return $this->arResult;
    }

    protected function setSections() {}
}
