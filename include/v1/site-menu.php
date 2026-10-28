<?php
/**
 * Меню сайта из стандартных файлов меню Битрикса (.top.menu.php, .bottom.menu.php, …).
 * Редактируются в админке: Контент → Структура сайта → «Редактировать меню».
 *
 * @var string $menuType тип меню (top, bottom, bottom_credits)
 * @var int $maxLevel уровней вложенности; подпункты — меню типа left в папке пункта (например, /about-us/.left.menu.php)
 */
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "api",
    [
        "ROOT_MENU_TYPE" => $menuType,
        "CHILD_MENU_TYPE" => "left",
        "MAX_LEVEL" => $maxLevel,
        "USE_EXT" => "Y",
        "ALLOW_MULTI_SELECT" => "N",
        "DELAY" => "N",
        "MENU_CACHE_TYPE" => "A",
        "MENU_CACHE_TIME" => "3600",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => [],
    ]
);
