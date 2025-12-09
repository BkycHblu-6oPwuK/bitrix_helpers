<?php
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "beeralex:favourite",
    ".default",
    [
        "PAGE_SIZE" => 1
    ]
);