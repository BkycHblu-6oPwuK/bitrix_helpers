<?php
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "beeralex:main",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);