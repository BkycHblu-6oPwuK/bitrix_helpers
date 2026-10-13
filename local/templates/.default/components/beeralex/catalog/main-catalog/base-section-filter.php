<?php

use Bitrix\Main\Loader;

if ($arParams['USE_FILTER']) {
    $arFilter = [
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ACTIVE" => "Y",
        "GLOBAL_ACTIVE" => "Y",
    ];
    if ((int)$arResult["VARIABLES"]["SECTION_ID"]) {
        $arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
    } elseif ('' != $arResult["VARIABLES"]["SECTION_CODE"]) {
        $arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
    }

    $obCache = new \CPHPCache();
    if ($obCache->InitCache(3600000, serialize($arFilter), "/iblock/catalog")) {
        $arCurSection = $obCache->GetVars();
    } elseif ($obCache->StartDataCache()) {
        $arCurSection = [];
        if (Loader::includeModule("iblock")) {
            $dbRes = \CIBlockSection::GetList([], $arFilter, false, ["ID"]);

            if (defined("BX_COMP_MANAGED_CACHE")) {
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache("/iblock/catalog");

                if ($arCurSection = $dbRes->Fetch()) {
                    $CACHE_MANAGER->RegisterTag("iblock_id_" . $arParams["IBLOCK_ID"]);
                }

                $CACHE_MANAGER->EndTagCache();
            } else {
                if (!$arCurSection = $dbRes->Fetch()) {
                    $arCurSection = [];
                }
            }
        }
        $obCache->EndDataCache($arCurSection);
    }
}
if (!isset($arCurSection)) {
    $arCurSection = [];
}
