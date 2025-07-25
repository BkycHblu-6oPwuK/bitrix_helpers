<?php

namespace Itb\EventHandlers;

class Iblock
{
    public static function onAfterIBlockElementSetPropertyValues($elementId, $iblockId)
    {
        \CIBlock::clearIblockTagCache($iblockId);
    }

    public static function onAfterIBlockElementSetPropertyValuesEx($elementId, $iblockId)
    {
        \CIBlock::clearIblockTagCache($iblockId);
    }

    public static function onBeforeIBlockElementAdd(&$arParams): bool
    {
        return true;
    }

    public static function onBeforeIBlockElementUpdate(&$arParams): bool
    {
        return true;
    }
    public static function onBeforeIBlockSectionUpdate(&$arParams): bool
    {
        if (isImport()) {
            unset($arParams['CODE']);
            unset($arParams['ACTIVE']);
        }

        return true;
    }

    public static function onBeforeIBlockPropertyUpdate(&$arFields)
    {
        return true;
    }
}
