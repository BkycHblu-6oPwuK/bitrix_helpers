<?
/** @var array $arResult */

use App\Main\Enum\ContentTypes;
use App\Http\GlobalResult;

GlobalResult::addPageData([
    'type' => ContentTypes::TWO_ARTICLES,
    'result' => $arResult
]);