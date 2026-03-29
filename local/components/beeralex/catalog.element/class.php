<?

use Beeralex\Catalog\Service\CatalogElementService;
use Beeralex\Core\Service\IblockService;
use Bitrix\Main\Loader;

CBitrixComponent::includeComponentClass("bitrix:catalog.element");

class BeeralexCatalogElement extends \CatalogElementComponent
{
    protected CatalogElementService $catalogElementService;

    public function onPrepareComponentParams($params)
    {
        if (!$params['IBLOCK_ID']) {
            $params['IBLOCK_ID'] = service(IblockService::class)->getIblockIdByCode('catalog');
        }
        $this->processServices($params);

        return parent::onPrepareComponentParams($params);
    }

    protected function processServices(array &$params)
    {
        $catalogElementService = $params['CATALOG_ELEMENT_SERVICE'] ?? null;
        if ($catalogElementService === null || !($catalogElementService instanceof CatalogElementService)) {
            Loader::requireModule('beeralex.catalog');
            $catalogElementService = service(CatalogElementService::class);
        }
        $this->catalogElementService = $catalogElementService;
        unset($params['CATALOG_ELEMENT_SERVICE']);
    }

    public function getCatalogElementService(): CatalogElementService
    {
        return $this->catalogElementService;
    }
}
