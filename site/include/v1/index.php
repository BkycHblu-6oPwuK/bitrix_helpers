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

// $APPLICATION->IncludeComponent(
//     "beeralex:content",
//     ".default",
//     [
//         'CODE' => 'main',
//         'CACHE_TIME' => 3600000,
//         'CACHE_TYPE' => 'A',
//     ],
//     false
// );