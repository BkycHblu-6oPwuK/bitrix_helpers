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
            $tab->input(
                'login',
                'Логин от ЛК ApiShip',
                'Основные'
            );

            $tab->input(
                'password',
                'Пароль от ЛК ApiShip',
            );

            $tab->checkbox(
                'is_test',
                'Тестовый контур ApiShip (api.dev.apiship.ru)'
            );

            $tab->input(
                'default_provider_connect_id',
                'ID подключения к СД по умолчанию (providerConnectId)',
            );

            $tab->checkbox(
                'logs_enable',
                'Включить логирование'
            );

            $tab->checkbox(
                'cache_enabled',
                'Кешировать запросы к ApiShip (расчёт тарифов, подключения, ПВЗ)'
            );

            $tab->input(
                'cache_ttl',
                'Время жизни кэша (секунды)',
                default: '3600'
            );
        }
    )->tab(
        'edit2',
        'Сопоставление статусов',
        'Сопоставление статусов ApiShip с статусами заказов и отгрузок Bitrix',
        function (SchemaTab $tab) {
            $orderStatuses = \Bitrix\Sale\OrderStatus::getAllStatusesNames(LANGUAGE_ID);
            $shipmentStatuses = \Bitrix\Sale\DeliveryStatus::getAllStatusesNames(LANGUAGE_ID);

            $tab->select(
                'send_on_status_id',
                'Статус заказа Bitrix для отправки в ApiShip',
                $orderStatuses,
            );

            // JSON-хранилище карт (скрываются JS-скриптом; значения задаются через таблицу ниже)
            $tab->input('order_status_map', 'Карта статусов заказа (JSON)');
            $tab->input('shipment_status_map', 'Карта статусов отгрузки (JSON)');

            // Встроенный набор ключей статусов ApiShip
            $apiShipKeys = \Beeralex\Apiship\Enum\StatusesApiShip::getMap();

            $rawOrderMap    = \Bitrix\Main\Config\Option::getRealValue('beeralex.apiship', 'order_status_map');
            $rawShipmentMap = \Bitrix\Main\Config\Option::getRealValue('beeralex.apiship', 'shipment_status_map');

            $savedOrderMap    = is_array($tmp = json_decode((string)$rawOrderMap, true)) ? $tmp : [];
            $savedShipmentMap = is_array($tmp = json_decode((string)$rawShipmentMap, true)) ? $tmp : [];

            // Добавляем ключи из сохранённых карт, которых нет в дефолтном наборе
            foreach (array_keys($savedOrderMap + $savedShipmentMap) as $extraKey) {
                if (!isset($apiShipKeys[$extraKey])) {
                    $apiShipKeys[$extraKey] = $extraKey;
                }
            }

            $buildOptions = static function (array $statuses, string $selected): string {
                $html = '<option value="">— не менять —</option>';
                foreach ($statuses as $id => $name) {
                    $esc = htmlspecialchars((string)$id, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $sel = ((string)$id === $selected) ? ' selected' : '';
                    $html .= '<option value="' . $esc . '"' . $sel . '>'
                        . htmlspecialchars((string)$name, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                        . '</option>';
                }
                return $html;
            };

            $rows = '';
            foreach ($apiShipKeys as $key => $label) {
                $keyEsc = htmlspecialchars($key, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $rows .= '<tr>'
                    . '<td style="padding:4px 8px">'
                    . htmlspecialchars($label, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                    . '</td>'
                    . '<td style="padding:4px 8px">'
                    . '<select class="js-apiship-order-sel" data-key="' . $keyEsc . '">'
                    . $buildOptions($orderStatuses, $savedOrderMap[$key] ?? '')
                    . '</select></td>'
                    . '<td style="padding:4px 8px">'
                    . '<select class="js-apiship-ship-sel" data-key="' . $keyEsc . '">'
                    . $buildOptions($shipmentStatuses, $savedShipmentMap[$key] ?? '')
                    . '</select></td>'
                    . '</tr>';
            }

            $html = '<table border="1" cellpadding="0" cellspacing="0"'
                . ' style="border-collapse:collapse;width:100%;margin-top:8px">'
                . '<thead><tr>'
                . '<th style="padding:6px 8px;text-align:left">Статус ApiShip</th>'
                . '<th style="padding:6px 8px;text-align:left">Статус заказа Bitrix (тип O)</th>'
                . '<th style="padding:6px 8px;text-align:left">Статус отгрузки Bitrix (тип D)</th>'
                . '</tr></thead>'
                . '<tbody>' . $rows . '</tbody>'
                . '</table>'
                . '<script>'
                . '(function(){'
                // Скрываем строки с сырыми JSON-инпутами
                . '["order_status_map","shipment_status_map"].forEach(function(n){'
                . 'var el=document.querySelector(\'input[name="\'+n+\'"]\');'
                . 'if(el){var r=el.closest("tr");if(r)r.style.display="none";}'
                . '});'
                . 'function serialize(){'
                . 'var om={},sm={};'
                . 'document.querySelectorAll(".js-apiship-order-sel").forEach(function(s){if(s.value!=="")om[s.dataset.key]=s.value;});'
                . 'document.querySelectorAll(".js-apiship-ship-sel").forEach(function(s){if(s.value!=="")sm[s.dataset.key]=s.value;});'
                . 'var oi=document.querySelector(\'input[name="order_status_map"]\');'
                . 'var si=document.querySelector(\'input[name="shipment_status_map"]\');'
                . 'if(oi)oi.value=JSON.stringify(om);'
                . 'if(si)si.value=JSON.stringify(sm);'
                . '}'
                . 'document.querySelectorAll(".js-apiship-order-sel,.js-apiship-ship-sel").forEach(function(s){s.addEventListener("change",serialize);});'
                . 'var f=document.querySelector("form");if(f)f.addEventListener("submit",serialize);'
                // Заполняем скрытые инпуты текущими значениями при загрузке
                . 'serialize();'
                . '}());'
                . '</script>';

            $tab->staticHtml('Сопоставление статусов ApiShip → Bitrix', $html);
        }
    )->tab(
        'edit3',
        'Отправитель',
        'Настройки отправителя ApiShip',
        function (SchemaTab $tab) {
            $tab->staticHtml('Информация об отправителе', '<a href="https://docs.apiship.ru/docs/api/order-service/create-order-async/">Подробнее в документации ApiShip</a>');
            $tab->input('sender_phone', 'Отправитель: телефон');
            $tab->input('sender_brand_name', 'Отправитель: бренд');
            $tab->input('sender_company_name', 'Отправитель: название компании');
            $tab->input('sender_company_inn', 'Отправитель: ИНН компании');
            $tab->input('sender_contact_name', 'Отправитель: ФИО контактного лица');
            $tab->input(name: 'sender_country_code', help: 'Отправитель: код страны (ISO, по умолчанию RU)', default: 'RU');
            $tab->input('sender_region', 'Отправитель: регион');
            $tab->input('sender_city', 'Отправитель: город');
            $tab->input('sender_street', 'Отправитель: улица');
            $tab->input('sender_house', 'Отправитель: дом');
            $tab->input('sender_block', 'Отправитель: корпус');
            $tab->input('sender_office', 'Отправитель: офис/квартира');
            $tab->input('sender_email', 'Отправитель: email');
            $tab->input('sender_address_string', 'Отправитель: адрес одной строкой');
            $tab->input('sender_lat', 'Отправитель: широта');
            $tab->input('sender_lng', 'Отправитель: долгота');
            $tab->input('sender_post_index', 'Отправитель: почтовый индекс');
            $tab->input('sender_area', 'Отправитель: район');
            $tab->input('sender_city_guid', 'Отправитель: GUID города в базе ФИАС');
            $tab->input('sender_comment', 'Отправитель: комментарий');
        }
    )->tab(
        'edit4',
        'Свойства и габариты',
        'Коды свойств заказа и размеры по умолчанию',
        function (SchemaTab $tab) {
            $tab->input('default_weight', 'Вес по умолчанию (г, если у товара не задан)', default: '1000');
            $tab->input('default_width',  'Ширина по умолчанию (см)', default: '20');
            $tab->input('default_height', 'Высота по умолчанию (см)', default: '20');
            $tab->input('default_length', 'Длина по умолчанию (см)', default: '20');
            $tab->input('prop_phone',           'Код свойства заказа: телефон получателя', default: 'PHONE');
            $tab->input('prop_fio',             'Код свойства заказа: ФИО получателя', default: 'FIO');
            $tab->input('prop_city',            'Код свойства заказа: город', default: 'CITY');
            $tab->input('prop_city_guid',       'Код свойства заказа: GUID города (ФИАС)', default: 'CITY_GUID');
            $tab->input('prop_address',         'Код свойства заказа: адрес доставки', default: 'ADDRESS');
            $tab->input('prop_pickup_point_id', 'Код свойства заказа: ID ПВЗ (pointOutId)', default: 'PICKUP_POINT_ID');
        }
    );
