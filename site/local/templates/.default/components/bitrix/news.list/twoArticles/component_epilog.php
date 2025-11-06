<?
/** @var array $arResult */

use Beeralex\Api\Domain\Content\Enum\ContentTypes;
use Beeralex\Api\GlobalResult;

GlobalResult::addPageData([
    'type' => ContentTypes::TWO_ARTICLES,
    'result' => $arResult
]);