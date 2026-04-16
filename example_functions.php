<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
/*
\Bitrix\Main\Diag\Debug::dumpToFile(['ID' => $id, 'fields' => $fields], "", "local/logs/1.log");

file_put_contents(
    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/1.log',
    print_r($data, true) . PHP_EOL,
    FILE_APPEND
);
*/
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
