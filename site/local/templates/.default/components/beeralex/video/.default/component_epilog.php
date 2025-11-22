<?
/** @var array $arResult */

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;

service(ApiResult::class)->addPageData([
    'type' => ContentTypes::VIDEO,
    'result' => $arResult
]);