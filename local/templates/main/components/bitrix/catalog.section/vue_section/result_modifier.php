<?

use App\Catalog\Helper\CatalogSectionHelper;
use Beeralex\Core\Helpers\PaginationHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['ITEMS'] = CatalogSectionHelper::getProductsForCard($arResult['ELEMENTS']);
$arResult['PAGINATION'] = PaginationHelper::toArray($arResult['NAV_RESULT']);

$this->getComponent()->setResultCacheKeys(['ITEMS', 'PAGINATION']);