<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit1',
        'Общие настройки',
        'Интеграция служб доставки через ApiShip',
        function (SchemaTab $tab) {
            $tab->staticText(
                'Токен доступа',
                'Токен API задаётся переменной APISHIP_TOKEN в .env и здесь не хранится.'
            );

            $tab->checkbox(
                'is_test',
                'Тестовый контур ApiShip (api.dev.apiship.ru)'
            );

            $tab->input(
                'custom_api_url',
                'Кастомный базовый URL API',
                'Прочие',
                null,
                false,
                ''
            );

            $tab->input(
                'default_provider_connect_id',
                'ID подключения к СД по умолчанию (providerConnectId)',
                'Основные',
                null,
                false,
                ''
            );

            $tab->input(
                'send_on_status_id',
                'Статус заказа Bitrix для отправки в ApiShip',
                'Основные',
                null,
                false,
                ''
            );

            $tab->staticText(
                '',
                'При переходе заказа в указанный статус он автоматически отправляется в ApiShip. Оставьте пустым, чтобы отключить автоотправку.'
            );

            $tab->checkbox(
                'ssl_verify',
                'Проверять SSL-сертификат',
                null,
                false,
                true
            );

            $tab->checkbox(
                'logs_enable',
                'Включить логирование'
            );
        }
    );
