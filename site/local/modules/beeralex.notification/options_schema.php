<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit1',
        'Настройки',
        'Общие настройки модуля',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'MODULE_ENABLED',
                'Включить модуль'
            );
        }
    );