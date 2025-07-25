<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Примерочная - интернет-магазин Dzhavadof");
$APPLICATION->SetTitle("Примерочная");
$APPLICATION->IncludeComponent(
    "itb:dressing",
    ".default",
    []
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
