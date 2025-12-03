<?

use Beeralex\Catalog\Service\CatalogElementService;
use Beeralex\Core\Service\IblockService;
use Bitrix\Main\Loader;

CBitrixComponent::includeComponentClass("bitrix:catalog.element");

class BeeralexCatalogElement extends \CatalogElementComponent
{
    public function onPrepareComponentParams($params)
    {
        if (!$params['IBLOCK_ID']) {
            $params['IBLOCK_ID'] = service(IblockService::class)->getIblockIdByCode('catalog');
        }

        if ($params['CATALOG_ELEMENT_SERVICE'] === null || !($params['CATALOG_ELEMENT_SERVICE'] instanceof CatalogElementService)) {
            Loader::requireModule('beeralex.catalog');
            $params['CATALOG_ELEMENT_SERVICE'] = service(CatalogElementService::class);
        }

        return parent::onPrepareComponentParams($params);
    }
}
