<?php
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "beeralex:menu",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
        "IBLOCK_ID" => $iblockId
    ]
);