<?php
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "beeralex:content",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
        "CODE" => $code
    ]
);