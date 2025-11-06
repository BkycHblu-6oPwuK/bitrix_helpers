<?
/** @var array $arResult */

use Beeralex\Api\Domain\Content\Enum\ContentTypes;
use Beeralex\Api\GlobalResult;

GlobalResult::addPageData([
    'type' => ContentTypes::MAIN_BANNER,
    'result' => $arResult['ITEMS']
]);