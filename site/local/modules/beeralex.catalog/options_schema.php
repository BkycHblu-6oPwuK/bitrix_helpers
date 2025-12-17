<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit2',
        'Локации',
        'Настройки клиента',
        function (SchemaTab $tab) {
            $tab->input(
                'API_KEY',
                'Ключ api для доступа к сервису локаций (если есть)',
                ''
            );

            $tab->input(
                'SECRET_KEY',
                'Секретный ключ api для доступа к сервису локаций (если есть)',
            );
        }
    );