<?php
declare(strict_types=1);

global $APPLICATION;
use Beeralex\Api\GlobalResult;

GlobalResult::$result = (array)$arResult['dto'];

$APPLICATION->SetPageProperty("title", "Статьи");
$APPLICATION->SetPageProperty("description", "Статьи описание");