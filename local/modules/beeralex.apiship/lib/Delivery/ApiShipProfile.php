<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Dto\CalculationRequestDto;
use Beeralex\Apiship\Dto\TariffDto;

Loc::loadMessages(__FILE__);

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
        return Loc::getMessage('BEERALEX_APISHIP_PROFILE_TITLE') ?: 'ApiShip: профиль ТК';
    }

    public static function getClassDescription(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_PROFILE_DESCRIPTION')
            ?: 'Профиль конкретной службы доставки из ЛК ApiShip. Выберите подключение из списка.';
    }

    /** Возвращает false, если подключение не выбрано — скрывает способ в оформлении. */
    public function isCompatible(Shipment $shipment): bool
    {
        [, $providerKey] = static::parseConnectionKey($this->getMainConfig());
        return $providerKey !== '';
    }

    public function getConfigStructure(): array
    {
        // тарифы строим по сохранённому подключению: у нового профиля его ещё нет
        $mainConfig = $this->getMainConfig();
        [, $savedProviderKey] = static::parseConnectionKey($mainConfig);
        $savedPickupType = (int)$mainConfig['PICKUP_TYPE'] ?: null;
        $savedDeliveryType = (int)$mainConfig['DELIVERY_TYPE'] ?: null;

        return [
            'MAIN' => [
                'NAME'  => Loc::getMessage('BEERALEX_APISHIP_PROFILE_CONFIG_MAIN') ?: 'Настройки профиля',
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
                        'OPTIONS' => ['' => '— все типы —'] + self::DELIVERY_TYPES,
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
                        'OPTIONS' => ['' => '— все типы —'] + self::PICKUP_TYPES,
                    ],
                    'POINT_IN_ID' => [
                        'TYPE' => 'STRING',
                        'NAME' => 'ID пункта приёма СД (pointInId) — при самозавозе',
                    ],
                ],
            ],
        ];
    }

    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();

        try {
            $tariffs = $this->getCalculatorService()->calculate(
                $this->buildCalculationRequest($shipment)
            );
        } catch (\InvalidArgumentException $e) {
            // не хватает данных для запроса (например, не выбран город)
            $result->addError(new Error($e->getMessage(), 'APISHIP_BAD_REQUEST'));
            return $result;
        } catch (\Throwable $e) {
            $result->addError(new Error($e->getMessage(), 'APISHIP_CALCULATE'));
            return $result;
        }

        $tariff = $this->pickTariff($tariffs);
        if ($tariff === null) {
            $result->addError(new Error(
                Loc::getMessage('BEERALEX_APISHIP_NO_TARIFF') ?: 'Не удалось рассчитать стоимость доставки',
                'APISHIP_NO_TARIFF'
            ));
            return $result;
        }

        $result->setDeliveryPrice($tariff->deliveryCost);
        $result->setPeriodFrom($tariff->daysMin);
        $result->setPeriodTo($tariff->daysMax);
        $result->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);

        return $result;
    }

    /**
     * Выбирает тариф: сначала по TARIFF_ID (если задан), иначе самый дешёвый.
     * Фильтрация по providerKey/deliveryType уже сделана на стороне API (см. buildCalculationRequest).
     *
     * @param TariffDto[] $tariffs
     */
    protected function pickTariff(array $tariffs): ?TariffDto
    {
        if ($tariffs === []) {
            return null;
        }

        $config = $this->getMainConfig();
        $tariffId = isset($config['TARIFF_ID']) && $config['TARIFF_ID'] !== ''
            ? (int)$config['TARIFF_ID']
            : null;

        if ($tariffId !== null) {
            foreach ($tariffs as $tariff) {
                if ($tariff->tariffId === $tariffId) {
                    return $tariff;
                }
            }
            return null;
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
        $key = (string)($config['CONNECTION_KEY'] ?? '');
        if ($key !== '' && str_contains($key, '|')) {
            [$connectId, $providerKey] = explode('|', $key, 2);
            return [$connectId, $providerKey];
        }
        return ['', (string)($config['PROVIDER_KEY'] ?? '')];
    }

    /**
     * Собирает CalculationRequestDto из Shipment и настроек профиля.
     *
     * @throws \InvalidArgumentException если для запроса не хватает обязательных данных
     */
    protected function buildCalculationRequest(Shipment $shipment): CalculationRequestDto
    {
        /** @var \Beeralex\Apiship\Options $options */
        $options = \service(\Beeralex\Apiship\Options::class);
        $config = $this->getMainConfig();
        $order = $shipment->getOrder();
        $to = [];

        if ($order !== null) {
            $properties = $order->getPropertyCollection();
            $cityVal = $properties->getItemByOrderPropertyCode($options->propCity)?->getValue();
            $cityGuidVal = $properties->getItemByOrderPropertyCode($options->propCityGuid)?->getValue();
            $addrVal = $properties->getItemByOrderPropertyCode($options->propAddress)?->getValue();
            $to = array_filter([
                'city'          => $cityVal,
                'cityGuid'      => $cityGuidVal,
                'addressString' => $addrVal,
                'countryCode'   => 'RU',
            ], static fn($v) => $v !== null && $v !== '');
        }

        [$connectId, $providerKey] = static::parseConnectionKey($config);
        $tariffId = isset($config['TARIFF_ID']) && $config['TARIFF_ID'] !== '' ? (int)$config['TARIFF_ID'] : 0;
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

        // если у ТК несколько подключений — считаем именно по выбранному: "{providerKey}.providerConnectId"
        $extraParams = [];
        if ($providerKey !== '' && ctype_digit($connectId) && (int)$connectId > 0) {
            $extraParams[$providerKey . '.providerConnectId'] = (int)$connectId;
        }

        $dto = CalculationRequestDto::make([
            'from'   => $options->sender,
            'to'     => $to,
            'weight' => $parcel['weight'],
            'width'  => $parcel['width'],
            'height' => $parcel['height'],
            'length' => $parcel['length'],
            'places' => [[
                'weight' => $parcel['weight'],
                'width'  => $parcel['width'],
                'height' => $parcel['height'],
                'length' => $parcel['length'],
            ]],
            'assessedCost'  => $parcel['assessedCost'],
            'providerKeys'  => $providerKey !== '' ? [$providerKey] : [],
            'tariffIds'     => $tariffId > 0 ? [$tariffId] : [],
            'deliveryTypes' => $deliveryType > 0 ? [$deliveryType] : [],
            'pickupTypes'   => $pickupType > 0 ? [$pickupType] : [],
            'pointInId'     => $pointInId,
            'extraParams'   => $extraParams,
        ]);

        $errors = $dto->validate();
        if ($errors !== []) {
            throw new \InvalidArgumentException(implode('; ', $errors));
        }

        return $dto;
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
        if (self::$connectionsCache !== null) {
            return self::$connectionsCache;
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

        return self::$connectionsCache = $connections;
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
        if (isset(self::$tariffOptionsCache[$providerKey])) {
            return self::$tariffOptionsCache[$providerKey];
        }

        $rows = \service(ProviderServiceContract::class)->getTariffs(['providerKey' => $providerKey]);

        $options = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $name = trim((string)($row['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }
            if ($pickupType !== null && (int)($row['pickupType'] ?? 0) !== $pickupType) {
                continue;
            }
            if ($deliveryType !== null && (int)($row['deliveryType'] ?? 0) !== $deliveryType) {
                continue;
            }

            $label = $name;
            $typeName = self::DELIVERY_TYPES[(string)($row['deliveryType'] ?? '')] ?? '';
            if ($typeName !== '') {
                $label .= ' · ' . $typeName;
            }
            $options[$id] = $label . ' (#' . $id . ')';
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return self::$tariffOptionsCache[$providerKey] = $options;
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
        $current = (string)($this->getMainConfig()[$field] ?? '');
        if ($current !== '' && !isset($options[$current])) {
            $options[$current] = sprintf($labelFormat, $current);
        }
        return $options;
    }
}
