<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    /** Логин от ЛК ApiShip. */
    public readonly string $login;

    /** Пароль от ЛК ApiShip. */
    public readonly string $password;
    
    /** Использовать тестовый контур ApiShip (api.dev.apiship.ru). */
    public readonly bool $isTest;

    /** Включить логирование запросов/ответов. */
    public readonly bool $logsEnable;

    /** ID подключения к службе доставки в ApiShip по умолчанию (providerConnectId). */
    public readonly int $defaultProviderConnectId;

    /**
     * Статус заказа Bitrix, при переходе в который заказ отправляется в ApiShip.
     * Пусто — автоотправка отключена (заказ создаётся вручную через API модуля).
     */
    public readonly string $sendOnStatusId;

    /**
     * Отправитель (склад магазина) — блок `sender` запроса создания заказа, ключи как в RecipientDto.
     * @var array<string,string>
     */
    public readonly array $sender;

    /**
     * Карта статусов ApiShip -> STATUS_ID заказа Bitrix (тип "O").
     * @var array<string,string>
     */
    public readonly array $orderStatusMap;

    /**
     * Карта статусов ApiShip -> STATUS_ID отгрузки Bitrix (тип "D").
     * @var array<string,string>
     */
    public readonly array $shipmentStatusMap;

    /** Кешировать запросы к ApiShip (расчёт, подключения, ПВЗ). */
    public readonly bool $cacheEnabled;

    /** Время жизни кэша в секундах. */
    public readonly int $cacheTtl;

    /** Вес по умолчанию (г), используется если вес товара не задан. */
    public readonly int $defaultWeight;

    /** Ширина по умолчанию (см). */
    public readonly int $defaultWidth;

    /** Высота по умолчанию (см). */
    public readonly int $defaultHeight;

    /** Длина по умолчанию (см). */
    public readonly int $defaultLength;

    /** Код свойства заказа: телефон получателя. */
    public readonly string $propPhone;

    /** Код свойства заказа: ФИО получателя. */
    public readonly string $propFio;

    /** Код свойства заказа: город. */
    public readonly string $propCity;

    /** Код свойства заказа: GUID города. */
    public readonly string $propCityGuid;

    /** Код свойства заказа: адрес. */
    public readonly string $propAddress;

    /** Код свойства заказа: ID ПВЗ (pointOutId). */
    public readonly string $propPickupPointId;

    protected function mapOptions(array $options): void
    {
        $this->login = (string)($options['login'] ?? '');
        $this->password = (string)($options['password'] ?? '');
        $this->isTest = ($options['is_test'] ?? '') === 'Y';
        $this->logsEnable = ($options['logs_enable'] ?? '') === 'Y';
        $this->defaultProviderConnectId = (int)($options['default_provider_connect_id'] ?? 0);
        $this->sendOnStatusId = (string)($options['send_on_status_id'] ?? '');
        $this->sender = array_filter([
            'phone'         => (string)($options['sender_phone'] ?? ''),
            'brandName'     => (string)($options['sender_brand_name'] ?? ''),
            'companyName'   => (string)($options['sender_company_name'] ?? ''),
            'companyInn'    => (string)($options['sender_company_inn'] ?? ''),
            'contactName'   => (string)($options['sender_contact_name'] ?? ''),
            'countryCode'   => (string)($options['sender_country_code'] ?? '') ?: 'RU',
            'region'        => (string)($options['sender_region'] ?? ''),
            'city'          => (string)($options['sender_city'] ?? ''),
            'street'        => (string)($options['sender_street'] ?? ''),
            'house'         => (string)($options['sender_house'] ?? ''),
            'block'         => (string)($options['sender_block'] ?? ''),
            'office'        => (string)($options['sender_office'] ?? ''),
            'email'         => (string)($options['sender_email'] ?? ''),
            'addressString' => (string)($options['sender_address_string'] ?? ''),
            'lat'           => (string)($options['sender_lat'] ?? ''),
            'lng'           => (string)($options['sender_lng'] ?? ''),
            'postIndex'     => (string)($options['sender_post_index'] ?? ''),
            'area'          => (string)($options['sender_area'] ?? ''),
            'cityGuid'      => (string)($options['sender_city_guid'] ?? ''),
            'comment'       => (string)($options['sender_comment'] ?? ''),
        ], static fn(string $v) => $v !== '');
        $this->orderStatusMap = $this->decodeMap($options['order_status_map'] ?? '');
        $this->shipmentStatusMap = $this->decodeMap($options['shipment_status_map'] ?? '');
        $this->cacheEnabled = ($options['cache_enabled'] ?? '') === 'Y';
        $this->cacheTtl = max(60, (int)($options['cache_ttl'] ?? 3600));
        $this->defaultWeight = max(1, (int)($options['default_weight'] ?? 1000));
        $this->defaultWidth = max(1, (int)($options['default_width'] ?? 20));
        $this->defaultHeight = max(1, (int)($options['default_height'] ?? 20));
        $this->defaultLength = max(1, (int)($options['default_length'] ?? 20));
        $this->propPhone = (string)($options['prop_phone'] ?? '') ?: 'PHONE';
        $this->propFio = (string)($options['prop_fio'] ?? '') ?: 'FIO';
        $this->propCity = (string)($options['prop_city'] ?? '') ?: 'CITY';
        $this->propCityGuid = (string)($options['prop_city_guid'] ?? '') ?: 'CITY_GUID';
        $this->propAddress = (string)($options['prop_address'] ?? '') ?: 'ADDRESS';
        $this->propPickupPointId = (string)($options['prop_pickup_point_id'] ?? '') ?: 'PICKUP_POINT_ID';
    }

    /**
     * Разбирает сохранённую карту статусов в ассоциативный массив <apiShipKey, bitrixStatusId>.
     * Хранится как JSON-строка (см. options_schema.php). Некорректное значение -> пустой массив
     * (маппер использует дефолтные карты из кода).
     *
     * @return array<string,string>
     */
    private function decodeMap(mixed $raw): array
    {
        if (is_array($raw)) {
            $decoded = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
        } else {
            $decoded = null;
        }

        if (!is_array($decoded)) {
            return [];
        }

        $map = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key) && (is_string($value) || is_int($value))) {
                $map[$key] = (string)$value;
            }
        }

        return $map;
    }

    public function getModuleId(): string
    {
        return 'beeralex.apiship';
    }
}
