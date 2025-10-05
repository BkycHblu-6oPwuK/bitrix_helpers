<?

use App\Catalog\Helper\CatalogSectionHelper;
use Itb\Core\Helpers\PaginationHelper;

 if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 */

$arResult['PAGINATION'] = PaginationHelper::toArray($arResult['NAV_RESULT']);

$GLOBALS['catalogSection'] = [
    'items' => $arResult['ITEMS'],
    'pagination' => $arResult['PAGINATION'],
];

$this->getComponent()->setResultCacheKeys(['ITEMS', 'PAGINATION']);

foreach($arResult["ITEMS"] as $arItemIndex => $arItem)
{
    $arResult["ITEMS"][$arItemIndex]['PROPERTIES']['PROP_IMAGE_LIST']['PATH'] = CFile::GetPath($arItem['PROPERTIES']['PROP_IMAGE_LIST']['VALUE']);
    $arResult["ITEMS"][$arItemIndex]['PROPERTIES']['PROP_IMAGE_LIST_WIDE']['PATH'] = CFile::GetPath($arItem['PROPERTIES']['PROP_IMAGE_LIST_WIDE']['VALUE']);
}
