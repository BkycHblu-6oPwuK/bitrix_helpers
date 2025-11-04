<?
/** @var array $arResult */

use App\Main\Enum\ContentTypes;
use App\Http\GlobalResult;

GlobalResult::addPageData([
    'type' => ContentTypes::MAIN_BANNER,
    'result' => $arResult['ITEMS']
]);