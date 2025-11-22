<?

use Beeralex\Catalog\Helper\CatalogSectionHelper;
use Beeralex\Core\Service\PaginationService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['ITEMS'] = CatalogSectionHelper::getProductsForCard($arResult['ELEMENTS']);
$arResult['PAGINATION'] = service(PaginationService::class)->toArray($arResult['NAV_RESULT']);

$this->getComponent()->setResultCacheKeys(['ITEMS', 'PAGINATION']);