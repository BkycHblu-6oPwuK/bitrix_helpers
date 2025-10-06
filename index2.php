<?

use Beeralex\Core\Helpers\IblockHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");
?>
<div class="section__container">

    <? $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "mainBanner",
        array(
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "ADD_SECTIONS_CHAIN" => "N",
            "AJAX_MODE" => "Y",
            "AJAX_OPTION_ADDITIONAL" => "",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "Y",
            "CACHE_TIME" => "86400",
            "CACHE_TYPE" => "A",
            "CHECK_DATES" => "Y",
            "COMPONENT_TEMPLATE" => "mainBanner",
            "DETAIL_URL" => "",
            "DISPLAY_BOTTOM_PAGER" => "N",
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "FIELD_CODE" => array(0 => "ID", 1 => "",),
            "FILTER_NAME" => "",
            "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
            "IBLOCK_ID" => IblockHelper::getIblockIdByCode("mainBanner"),
            "IBLOCK_TYPE" => "content",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "INCLUDE_SUBSECTIONS" => "Y",
            "MESSAGE_404" => "",
            "NEWS_COUNT" => "20",
            "PAGER_BASE_LINK" => "",
            "PAGER_BASE_LINK_ENABLE" => "Y",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_PARAMS_NAME" => "arrPager",
            "PAGER_SHOW_ALL" => "Y",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => "",
            "PAGER_TITLE" => "Новости",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "PROPERTY_CODE" => array(0 => "", 1 => "DESCRIPTION", 2 => "",),
            "SET_BROWSER_TITLE" => "N",
            "SET_LAST_MODIFIED" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_STATUS_404" => "N",
            "SET_TITLE" => "N",
            "SHOW_404" => "N",
            "SORT_BY1" => "ACTIVE_FROM",
            "SORT_BY2" => "SORT",
            "SORT_ORDER1" => "DESC",
            "SORT_ORDER2" => "ASC",
            "STRICT_SECTION_CHECK" => "N"
        )
    );
    ?>
</div>
<?

$APPLICATION->IncludeComponent(
    "beeralex:content",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>