<?

use Beeralex\Core\Assets\Vite;

// Vite::getInstance()->include('src/app/main/index.js');

$APPLICATION->IncludeComponent(
    "beeralex:content",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

?>