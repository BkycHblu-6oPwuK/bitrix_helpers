<?

use Beeralex\Api\Domain\Iblock\Catalog\CatalogSectionDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var BeeralexCatalogSection $component
 */

$component = $this->getComponent();
$arResult['ITEMS'] = $component->getCatalogService()->getProductsWithOffers($arResult['ELEMENTS'], false, false, true, true);
$arResult['DTO'] = CatalogSectionDTO::make($arResult);

$component->setResultCacheKeys(['DTO']);