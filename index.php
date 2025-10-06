<?

use Beeralex\Core\Assets\Vite;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");

Vite::getInstance()->include('src/app/main/index.js');

$APPLICATION->IncludeComponent(
    "beeralex:index",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>