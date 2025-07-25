<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "На этой страницы собраны все избранные товары, которые вам понравились или вы хотели бы их купить.");
$APPLICATION->SetPageProperty("title", "Избранные товары - интернет-магазин Dzhavadof");
$APPLICATION->SetTitle("Избранное");
$APPLICATION->IncludeComponent(
    "itb:favourite",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600000",
    ]
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
