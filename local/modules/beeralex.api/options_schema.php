<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit1',
        'Общие настройки',
        'API сайта',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'SPA_API_ENABLED',
                'Режим SPA API',
            );
        }
    );