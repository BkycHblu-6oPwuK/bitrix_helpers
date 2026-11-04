<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Beeralex\Apiship\Enum\DeliveryType;
use Beeralex\Apiship\Exception\ApiShipProfileCalculationException;
use Beeralex\Apiship\Enum\PickupType;
use Beeralex\Apiship\Service\ProfileCalculator;
use Beeralex\Apiship\Support\CalculatedTariffStore;
use Beeralex\Apiship\Support\ProfileConfig;
use Beeralex\Apiship\Support\ProfileFormOptions;
use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

/**
 * Профиль доставки ApiShip. Только Bitrix-обвязка: форма настроек, совместимость, маппинг результата.
 * Расчёт — {@see ProfileCalculator}, списки формы — {@see ProfileFormOptions}.
 */
class ApiShipProfile extends Base
{
    /** @var string */
    protected $handlerCode = 'BEERALEX_APISHIP';

    protected static $isProfile = true;

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

    /** Скрывает способ в оформлении, пока не выбрано подключение. */
    public function isCompatible(Shipment $shipment): bool
    {
        return $this->profileConfig()->providerKey !== '';
    }

    public function getConfigStructure(): array
    {
        $config = $this->profileConfig();
        $form = \service(ProfileFormOptions::class);

        return [
            'MAIN' => [
                'NAME'  => 'Настройки профиля',
                'ITEMS' => [
                    'CONNECTION_KEY' => [
                        'TYPE'     => 'ENUM',
                        'NAME'     => 'Подключение к СД (из ЛК ApiShip)',
                        'OPTIONS'  => $form->connectionOptions((string)($this->getMainConfig()['CONNECTION_KEY'] ?? '')),
                        'REQUIRED' => 'Y',
                    ],
                    'DELIVERY_TYPE' => [
                        'TYPE'    => 'ENUM',
                        'NAME'    => 'Тип доставки (как клиент получит заказ)',
                        'OPTIONS' => ['' => '— все типы —'] + DeliveryType::options(),
                    ],
                    'TARIFF_ID' => [
                        'TYPE'     => 'ENUM',
                        'MULTIPLE' => 'Y',
                        'NAME'     => $config->providerKey === ''
                            ? 'Тариф (список появится после сохранения подключения)'
                            : 'Тариф (пусто — расчёт всех тарифов ТК, выбирается самый дешёвый)',
                        'OPTIONS'  => $form->tariffOptions($config),
                    ],
                    'PICKUP_TYPE' => [
                        'TYPE'    => 'ENUM',
                        'NAME'    => 'Тип забора (как заказ будет передан в СД)',
                        'OPTIONS' => ['' => '— все типы —'] + PickupType::options(),
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
            $tariff = \service(ProfileCalculator::class)->calculate($shipment, $this->profileConfig());
        } catch (ApiShipProfileCalculationException $e) {
            $result->addError(new Error('APISHIP_CALCULATION_ERROR', $e->getMessage()));
            return $result;
        }

        if ($tariff === null) {
            $result->addError(new Error('APISHIP_NO_TARIFF', 'Не удалось рассчитать стоимость доставки'));
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

    protected function profileConfig(): ProfileConfig
    {
        return ProfileConfig::fromArray($this->getMainConfig());
    }

    /** @return array<string,mixed> */
    protected function getMainConfig(): array
    {
        return $this->getConfigValues()['MAIN'] ?? [];
    }
}