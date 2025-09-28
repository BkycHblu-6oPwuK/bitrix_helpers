<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");

$APPLICATION->IncludeComponent(
    "itb:index",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>