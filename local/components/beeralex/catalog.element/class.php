<?

use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Service\IblockService;
use Bitrix\Main\Loader;

CBitrixComponent::includeComponentClass("bitrix:catalog.element");

class BeeralexCatalogElement extends \CatalogElementComponent
{
    protected CatalogService $catalogService;

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
        $catalogService = $params['CATALOG_SERVICE'] ?? null;
        if ($catalogService === null || !($catalogService instanceof CatalogService)) {
            Loader::requireModule('beeralex.catalog');
            $catalogService = service(CatalogService::class);
        }
        $this->catalogService = $catalogService;
        unset($params['CATALOG_SERVICE']);
    }

    public function getCatalogService(): CatalogService
    {
        return $this->catalogService;
    }
}
