<?php

namespace App\Agents;

use Bitrix\Main\Data\Cache;

class ClearCache
{
    public static function exec()
    {
        Cache::delayedDelete();
        return '\\' . __METHOD__ . '();';
    }
}
