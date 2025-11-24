<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit1',
        'Настройки',
        'Настройки модуля Beeralex Catalog',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'BEERALEX_CATALOG_MIN_PRICE_IS_DISCOUNT',
                'Минимальная цена товара - это скидочная цена',
                null,
                false,
                false
            );
        }
    );