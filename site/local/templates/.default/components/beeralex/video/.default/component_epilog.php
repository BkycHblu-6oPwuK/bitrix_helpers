<?
/** @var array $arResult */

use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Api\GlobalResult;

GlobalResult::addPageData([
    'type' => ContentTypes::VIDEO,
    'result' => $arResult
]);