<?php

use Beeralex\Core\Repository\IblockRepository;

$sectionId = null;
if ($arResult["VARIABLES"]["SECTION_ID"]) {
	$sectionId = (int)$arResult["VARIABLES"]["SECTION_ID"];
} elseif ($arResult["VARIABLES"]["SECTION_CODE"]) {
	$iblockRepository = new IblockRepository((int)$arParams["IBLOCK_ID"]);
	$sectionId = (int)($iblockRepository->getIblockSectionRepository()->query()->setSelect(['ID'])->where('CODE', $arResult["VARIABLES"]["SECTION_CODE"])->setCacheTtl($arParams["CACHE_TIME"])->fetch()['ID']) ?: null;
}