<?php
namespace App\Catalog\Type;

use App\Catalog\Helper\CatalogHelper;
use App\Catalog\Type\Contracts\CatalogContextContract;
use App\Catalog\Type\Contracts\CatalogSwitcherContract;
use App\Iblock\Model\SectionModel;
use Beeralex\Core\Helpers\IblockHelper;

class CatalogTypesServiceProvider extends \Beeralex\Core\DI\AbstractServiceProvider
{
    public function registerServices(): void
    {
        $this->bind(CatalogSwitcherContract::class, CatalogSwitcher::class);
        $this->bind(CatalogContextContract::class, CatalogContext::class, fn() => [
            $this->locator->get(CatalogSwitcherContract::class),
            SectionModel::compileEntityByIblock(IblockHelper::getIblockIdByCode('catalog'))
        ]);
    }
}