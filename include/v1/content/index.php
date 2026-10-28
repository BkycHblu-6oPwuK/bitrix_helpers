<?

use Beeralex\Core\Service\IblockService;

global $APPLICATION;

if (!isset($code)) {
    throw new \InvalidArgumentException('Code is not set');
}

$GLOBALS['arrFilter'] = ['CODE' => $code];

$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "content",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600000",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => service(IblockService::class)->getIblockIdByCode('content'),
        "NEWS_COUNT" => "999",
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "DISPLAY_DATE" => "N",
        "SORT_BY1" => 'ID',
        "SORT_ORDER1" => 'ASC',
        'PROPERTY_CODE' => [
            'CONTENT',
        ],
    ],
    false
);
