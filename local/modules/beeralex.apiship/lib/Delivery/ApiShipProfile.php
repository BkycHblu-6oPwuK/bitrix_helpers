<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Dto\CalculationContext;
use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\CalculatedTariffStore;
use Beeralex\Apiship\Support\CalculationValidator;
use Beeralex\Apiship\Support\OrderAddressReader;
use Beeralex\Apiship\Support\ProfileConfig;
use Beeralex\Apiship\Support\ShipmentParcelBuilder;

/**
 * Профиль доставки ApiShip — конкретная служба доставки из ЛК ApiShip.
 * Добавляется в Bitrix вручную: «добавить профиль» → выбрать подключение из списка.
 *
 * Подключения и тарифы в настройках подтягиваются из ApiShip. Тарифы фильтруются по ТК
 * из уже сохранённого подключения, поэтому у нового профиля список тарифов появляется
 * после первого сохранения. Вся логика расчёта живёт здесь;
 * родитель (ApiShipHandler) — только контейнер профилей.
 */
class ApiShipProfile extends Base
{
    /** @var string */
    protected $handlerCode = 'BEERALEX_APISHIP';

    protected static $isProfile = true;

    /** Как клиент получит заказ (значения совпадают с lists/deliveryTypes). */
    private const DELIVERY_TYPES = [
        '1' => 'До двери',
        '2' => 'До ПВЗ',
    ];

    /** Как заказ будет передан в СД (значения совпадают с lists/pickupTypes). */
    private const PICKUP_TYPES = [
        '1' => 'Забор от двери',
        '2' => 'Самозавоз (привоз на склад СД)',
    ];

    /** @var array<int,array{id:string,providerKey:string,name:string}>|null Кеш подключений в рамках хита. */
    private static ?array $connectionsCache = null;

    /** @var array<string,array<int,string>> Кеш списков тарифов по providerKey в рамках хита. */
    private static array $tariffOptionsCache = [];

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->setTrackingClass(Tracking\ApiShipTracking::class);
    }

    public static function isProfile(): bool
    {
        return true;
    }

    public static function canHasProfiles(): bool
    {
        return false;
    }

    public function getProfilesList(): array
    {
        return [];
    }

    public static function getClassTitle(): string
    {
        return 'ApiShip: профиль ТК';
    }

    public static function getClassDescription(): string
    {
        return 'Профиль конкретной службы доставки из ЛК ApiShip. Выберите подключение из списка.';
    }

    /** Возвращает false, если подключение не выбрано — скрывает способ в оформлении. */
    public function isCompatible(Shipment $shipment): bool
    {
        return $this->profileConfig()->providerKey !== '';
    }

    public function getConfigStructure(): array
    {
        // тарифы строим по сохранённому подключению: у нового профиля его ещё нет
        $config = $this->profileConfig();
        $savedProviderKey = $config->providerKey;
        $savedPickupType = $config->pickupType ?: null;
        $savedDeliveryType = $config->deliveryType ?: null;

        return [
            'MAIN' => [
                'NAME'  => 'Настройки профиля',
                'ITEMS' => [
                    'CONNECTION_KEY' => [
                        'TYPE'     => 'ENUM',
                        'NAME'     => 'Подключение к СД (из ЛК ApiShip)',
                        'OPTIONS'  => $this->connectionOptions(),
                        'REQUIRED' => 'Y',
                    ],
                    'DELIVERY_TYPE' => [
                        'TYPE'    => 'ENUM',
                        'NAME'    => 'Тип доставки (как клиент получит заказ)',
                        'OPTIONS' => ['' => '— все типы —'] + static::DELIVERY_TYPES,
                    ],
                    'TARIFF_ID' => [
                        'TYPE'    => 'ENUM',
                        'MULTIPLE' => 'Y',
                        'NAME'    => $savedProviderKey === ''
                            ? 'Тариф (список появится после сохранения подключения)'
                            : 'Тариф (пусто — расчёт всех тарифов ТК, выбирается самый дешёвый)',
                        'OPTIONS' => $this->tariffOptions($savedProviderKey, $savedPickupType, $savedDeliveryType),
                    ],
                    'PICKUP_TYPE' => [
                        'TYPE'    => 'ENUM',
                        'NAME'    => 'Тип забора (как заказ будет передан в СД)',
                        'OPTIONS' => ['' => '— все типы —'] + static::PICKUP_TYPES,
                    ],
                    'POINT_IN_ID' => [
                        'TYPE' => 'STRING',
                        'NAME' => 'ID пункта приёма СД (pointInId) — при типе забора самовывоз',
                    ],
                ],
            ],
        ];
    }

    protected function profileConfig(): ProfileConfig
    {
        return ProfileConfig::fromArray($this->getMainConfig());
    }

    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();

        try {
            $context = $this->buildCalculationContext($shipment);
        } catch (\InvalidArgumentException $e) {
            $result->addError(new Error($e->getMessage(), 'APISHIP_BAD_REQUEST'));
            return $result;
        }

        try {
            $tariffs = $this->getCalculatorService()->calculate($context);
        } catch (\Throwable $e) {
            $result->addError(new Error($e->getMessage(), 'APISHIP_CALCULATE'));
            return $result;
        }

        $tariff = $this->pickTariff($tariffs);
        if ($tariff === null) {
            $result->addError(new Error(
                'Не удалось рассчитать стоимость доставки',
                'APISHIP_NO_TARIFF'
            ));
            return $result;
        }

        $result->setDeliveryPrice($tariff->deliveryCost);
        $result->setPeriodFrom($tariff->daysMin);
        $result->setPeriodTo($tariff->daysMax);
        $result->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);
        $order = $shipment->getOrder();
        if ($order !== null) {
            \service(CalculatedTariffStore::class)->save($order, (int)$this->getId(), $tariff);
        }
        return $result;
    }

    /**
     * Выбирает тариф: сначала по TARIFF_ID (если задан), иначе самый дешёвый.
     *
     * @param TariffDto[] $tariffs
     */
    protected function pickTariff(array $tariffs): ?TariffDto
    {
        $tariffIds = $this->profileConfig()->tariffIds;
        if ($tariffIds !== []) {
            $tariffs = array_filter(
                $tariffs,
                static fn(TariffDto $t) => in_array($t->tariffId, $tariffIds, true)
            );
        }

        $cheapest = null;
        foreach ($tariffs as $tariff) {
            if ($cheapest === null || $tariff->deliveryCost < $cheapest->deliveryCost) {
                $cheapest = $tariff;
            }
        }

        return $cheapest;
    }

    /**
     * Разбирает CONNECTION_KEY = "{connectId}|{providerKey}".
     * Также поддерживает старый формат PROVIDER_KEY для обратной совместимости.
     *
     * @param array<string,mixed> $config
     * @return array{0:string,1:string} [$connectId, $providerKey]
     */
    public static function parseConnectionKey(array $config): array
    {
        $c = ProfileConfig::fromArray($config);
        return [$c->connectId, $c->providerKey];
    }

    /**
     * Собирает CalculationContext из Shipment и настроек профиля.
     *
     * @throws \InvalidArgumentException если для запроса не хватает обязательных данных
     */
    protected function buildCalculationContext(Shipment $shipment): CalculationContext
    {
        /** @var Options $options */
        $options = \service(Options::class);
        $config = $this->getMainConfig();
        $order = $shipment->getOrder();
        $to = $order === null ? [] : array_filter(
            \service(OrderAddressReader::class)->read($order),
            static fn($v) => $v !== null && $v !== ''
        );

        [$connectId, $providerKey] = static::parseConnectionKey($config);

        $deliveryType = (int)($config['DELIVERY_TYPE'] ?? 0);
        $pickupType = (int)($config['PICKUP_TYPE'] ?? 0);
        // склад СД имеет смысл только при самозавозе
        $pointInId = $pickupType === 2 ? (int)($config['POINT_IN_ID'] ?? 0) : 0;

        // вес, габариты и оценочная стоимость — по реальным товарам отгрузки
        $parcel = ShipmentParcelBuilder::build($shipment, [
            'weight' => $options->defaultWeight,
            'width'  => $options->defaultWidth,
            'height' => $options->defaultHeight,
            'length' => $options->defaultLength,
        ]);

        // если у ТК несколько подключений — считаем именно по выбранному
        $extraParams = [];
        if ($providerKey !== '' && ctype_digit($connectId) && (int)$connectId > 0) {
            $extraParams[$providerKey . '.providerConnectId'] = (int)$connectId;
        }

        $sender = $options->sender;
        $context = CalculationContext::make([
            'from'   => [
                'city'          => $sender['city'] ?? null,
                'cityGuid'      => $sender['cityGuid'] ?? null,
                'addressString' => $sender['addressString'] ?? null,
                'countryCode'   => $sender['countryCode'] ?? 'RU',
            ],
            'to'     => $to,
            'places' => [[
                'weight' => $parcel['weight'],
                'width'  => $parcel['width'],
                'height' => $parcel['height'],
                'length' => $parcel['length'],
            ]],
            'assessedCost'  => $parcel['assessedCost'],
            'providerKeys'  => $providerKey !== '' ? [$providerKey] : [],
            'tariffIds' => $this->profileConfig()->tariffIds,
            'deliveryTypes' => $deliveryType > 0 ? [$deliveryType] : [],
            'pickupTypes'   => $pickupType > 0 ? [$pickupType] : [],
            'pointInId'     => $pointInId,
            'extraParams'   => $extraParams,
        ]);

        $errors = CalculationValidator::validate($context);
        if ($errors !== []) {
            throw new \InvalidArgumentException(implode('; ', $errors));
        }

        return $context;
    }

    protected function getCalculatorService(): CalculatorServiceContract
    {
        return \service(CalculatorServiceContract::class);
    }

    /**
     * Секция MAIN конфига профиля.
     *
     * @return array<string,mixed>
     */
    protected function getMainConfig(): array
    {
        return $this->getConfigValues()['MAIN'] ?? [];
    }

    /**
     * Подключения из ЛК в нормализованном виде.
     *
     * @return array<int,array{id:string,providerKey:string,name:string}>
     */
    private function getConnections(): array
    {
        if (static::$connectionsCache !== null) {
            return static::$connectionsCache;
        }

        $connections = [];
        foreach (\service(ConnectionServiceContract::class)->getConnections() as $conn) {
            $id = (string)($conn['id'] ?? '');
            $providerKey = (string)($conn['providerKey'] ?? '');
            if ($id === '' || $providerKey === '') {
                continue;
            }
            $connections[] = [
                'id'          => $id,
                'providerKey' => $providerKey,
                'name'        => (string)($conn['name'] ?? $providerKey),
            ];
        }

        return static::$connectionsCache = $connections;
    }

    /**
     * Подключения для SELECT: ключ = "{id}|{providerKey}", значение = название.
     * Формат ключа позволяет хранить оба поля в одном конфиг-значении без лишних API-запросов.
     *
     * @return array<string,string>
     */
    private function connectionOptions(): array
    {
        $options = ['' => '— выберите подключение —'];

        try {
            foreach ($this->getConnections() as $conn) {
                $options["{$conn['id']}|{$conn['providerKey']}"] = "{$conn['name']} ({$conn['providerKey']})";
            }
        } catch (\Throwable $e) {
            $options[''] = 'Ошибка загрузки подключений: ' . $e->getMessage();
        }

        return $this->keepCurrentValue($options, 'CONNECTION_KEY', 'Подключение %s (нет в ЛК ApiShip)');
    }

    /**
     * Тарифы ТК из сохранённого подключения. Без подключения — только «все тарифы».
     *
     * @return array<int|string,string>
     */
    private function tariffOptions(string $providerKey, ?int $pickupType = null, ?int $deliveryType = null): array
    {
        $options = ['' => '— все тарифы ТК —'];

        if ($providerKey !== '') {
            try {
                $options += $this->loadTariffOptions($providerKey, $pickupType, $deliveryType);
            } catch (\Throwable) {
                // API недоступен — остаётся «все тарифы», сохранённое значение вернёт keepCurrentValue
            }
        }

        return $this->keepCurrentValue($options, 'TARIFF_ID', 'Тариф #%s (нет в списке)');
    }

    /**
     * @return array<int,string> id тарифа => «Название · До двери (#26)»
     */
    private function loadTariffOptions(string $providerKey, ?int $pickupType = null, ?int $deliveryType = null): array
    {
        $cacheKey = $providerKey . '|' . (int)$pickupType . '|' . (int)$deliveryType;
        if (isset(static::$tariffOptionsCache[$cacheKey])) {
            return static::$tariffOptionsCache[$cacheKey];
        }

        $rows = \service(ProviderServiceContract::class)->getTariffs(['providerKey' => $providerKey]);

        $options = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $name = trim((string)($row['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }

            $rowDelivery = static::intValues($row, 'deliveryTypes', 'deliveryType');
            $rowPickup = static::intValues($row, 'pickupTypes', 'pickupType');

            // если API не вернул поле — не прячем тариф
            if ($pickupType !== null && $rowPickup !== [] && !in_array($pickupType, $rowPickup, true)) {
                continue;
            }
            if ($deliveryType !== null && $rowDelivery !== [] && !in_array($deliveryType, $rowDelivery, true)) {
                continue;
            }

            $typeNames = array_filter(array_map(
                static fn(int $t) => static::DELIVERY_TYPES[(string)$t] ?? '',
                $rowDelivery
            ));
            $label = $name . ($typeNames ? ' · ' . implode(' / ', $typeNames) : '');
            $options[$id] = $label . ' (#' . $id . ')';
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return static::$tariffOptionsCache[$cacheKey] = $options;
    }

    /** @return int[] значения из массивного или скалярного поля строки API */
    private static function intValues(array $row, string $plural, string $single): array
    {
        $raw = $row[$plural] ?? $row[$single] ?? [];
        return array_values(array_filter(array_map('intval', (array)$raw)));
    }

    /**
     * Если сохранённого значения нет в списке (сбой API, тариф убрали в ЛК), добавляет его,
     * иначе при следующем сохранении формы настройка молча сбросится.
     *
     * @param array<int|string,string> $options
     * @return array<int|string,string>
     */
    private function keepCurrentValue(array $options, string $field, string $labelFormat): array
    {
        foreach ((array)($this->getMainConfig()[$field] ?? []) as $current) {
            $current = (string)$current;
            if ($current !== '' && !isset($options[$current])) {
                $options[$current] = sprintf($labelFormat, $current);
            }
        }
        return $options;
    }
}
