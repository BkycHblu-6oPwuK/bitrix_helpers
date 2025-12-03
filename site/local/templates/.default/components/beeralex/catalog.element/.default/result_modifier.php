<?

use Beeralex\Api\Domain\Iblock\Catalog\CatalogElementDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$elementData = $arParams['CATALOG_ELEMENT_SERVICE']->getElementData((int)$arResult['ID'], (int)$arParams['OFFER_ID'] ?? null);
$arResult['DTO'] = CatalogElementDTO::makeFrom($elementData, $arResult);
$this->getComponent()->setResultCacheKeys(['DTO']);