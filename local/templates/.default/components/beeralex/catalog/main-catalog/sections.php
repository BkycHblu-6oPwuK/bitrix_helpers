<?php

use Beeralex\Core\Service\IblockService;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->IncludeComponent(
	"beeralex:catalog.section.list",
	".default",
	array(
		"IBLOCK_ID" => service(IblockService::class)->getIblockIdByCode('catalog'),
		"CACHE_FILTER" => $arParams["CACHE_FILTER"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	),
	$component
);