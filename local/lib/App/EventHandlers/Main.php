<?php

namespace App\EventHandlers;

use Bitrix\Main\DI\ServiceLocator;
use App\Catalog\Type\Contracts\CatalogSwitcherContract;
use App\Catalog\Type\Enum\TypesCatalog;
use Beeralex\Core\Config\Config;

class Main
{
    public static function onPageStart()
    {
        /**
         * @var CatalogSwitcherContract $swither
         */
        if (Config::getInstance()['SWITH_CATALOG_TYPES']) {
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
