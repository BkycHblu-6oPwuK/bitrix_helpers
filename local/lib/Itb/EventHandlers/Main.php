<?php

namespace Itb\EventHandlers;

use Bitrix\Main\DI\ServiceLocator;
use Itb\Catalog\Types\Contracts\CatalogSwitcherContract;
use Itb\Catalog\Types\Enum\TypesCatalog;
use Itb\Core\Config;

class Main
{
    public static function onPageStart()
    {
        /**
         * @var CatalogSwitcherContract $swither
         */
        if (Config::getInstance()->enableSwitchCatalogType) {
            $swither = ServiceLocator::getInstance()->get(CatalogSwitcherContract::class);
            if ($swither->get() === TypesCatalog::ALL) {
                $catalogTypes = TypesCatalog::cases();
                foreach ($catalogTypes as $type) {
                    if (str_contains($_SERVER['REQUEST_URI'], "/$type->value/")) {
                        $swither->set($type);
                        break;
                    }
                }
                // fallback тип по умолчанию 1
                if ($swither->get() === TypesCatalog::ALL) {
                    $swither->set($catalogTypes[0]);
                }
            }
        }
    }
}
