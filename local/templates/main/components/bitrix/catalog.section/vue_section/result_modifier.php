<?

use Itb\Catalog\CatalogSection;
use Itb\Core\Helpers\PaginationHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['ITEMS'] = CatalogSection::getProductsForCard($arResult['ELEMENTS']);
$arResult['PAGINATION'] = PaginationHelper::toArray($arResult['NAV_RESULT']);

$this->getComponent()->setResultCacheKeys(['ITEMS', 'PAGINATION']);