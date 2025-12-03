<?

use Beeralex\Api\Domain\Iblock\Catalog\CatalogSectionDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();
$arResult['ITEMS'] = $arParams['CATALOG_SERVICE']->getProductsWithOffers($arResult['ELEMENTS']);
$arResult['DTO'] = CatalogSectionDTO::make($arResult);

$this->getComponent()->setResultCacheKeys(['DTO']);