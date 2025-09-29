<?

use Itb\Core\Assets\Vite;

Vite::getInstance()->include('src/app/main/index.js');

$APPLICATION->IncludeComponent(
    "itb:content",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

?>