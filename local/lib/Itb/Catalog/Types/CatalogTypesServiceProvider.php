<?php
namespace Itb\Catalog\Types;

use Itb\Catalog\CatalogHelper;
use Itb\Catalog\Types\Contracts\CatalogContextContract;
use Itb\Catalog\Types\Contracts\CatalogSwitcherContract;

class CatalogTypesServiceProvider extends \Itb\Core\DI\AbstractServiceProvider
{
    public function registerServices(): void
    {
        $this->bind(CatalogSwitcherContract::class, CatalogSwitcher::class);
        $this->bind(CatalogContextContract::class, CatalogContext::class, fn() => [
            $this->locator->get(CatalogSwitcherContract::class),
            CatalogHelper::getCalogSectionsEntity()
        ]);
    }
}