<?

use Beeralex\Api\Domain\Iblock\Catalog\CatalogElementDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var BeeralexCatalogElement $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$elementData = $component->getCatalogElementService()->getElementData((int)$arResult['ID'], $arParams['OFFER_ID'] ? (int)$arParams['OFFER_ID'] : null);
$arResult['DTO'] = CatalogElementDTO::makeFrom($elementData, $arResult);
$component->setResultCacheKeys(['DTO']);