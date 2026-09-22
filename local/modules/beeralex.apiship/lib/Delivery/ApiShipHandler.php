<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Dto\CalculationRequestDto;
use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Exception\ApiShipException;

Loc::loadMessages(__FILE__);

/**
 * Единый обработчик доставки ApiShip.
 *
 * Реализует принцип "1 обработчик, максимум управления из ЛК ApiShip" (см. README задачи
 * и docs/DECISIONS.md ADR-004): в Bitrix создаётся минимум настроек (провайдер+тариф в CONFIG,
 * либо ничего — тогда расчёт идёт по всем ТК), а профили (ApiShipProfile) позволяют завести
 * отдельную "службу доставки" под конкретную ТК/тариф без написания нового класса.
 *
 * Расчёт (calculateConcrete) идёт через CalculatorServiceContract — «богатое API» модуля,
 * без завязки на HTTP/фронт (см. docs/ARCHITECTURE.md).
 */
class ApiShipHandler extends Base
{
    /** @var string */
    protected $handlerCode = 'BEERALEX_APISHIP';

    protected static $canHasProfiles = true;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->setTrackingClass(Tracking\ApiShipTracking::class);
    }

    /**
     * Регистрация обработчика в списке классов служб доставки Bitrix.
     * Обработчик события sale::onSaleDeliveryHandlersClassNamesBuildList.
     */
    public static function onSaleDeliveryHandlersClassNamesBuildList(): EventResult
    {
        return new EventResult(
            EventResult::SUCCESS,
            [
                self::class => __FILE__,
            ],
            'sale'
        );
    }

    public static function getClassTitle(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_HANDLER_TITLE') ?: 'ApiShip';
    }

    public static function getClassDescription(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_HANDLER_DESCRIPTION')
            ?: 'Единая доставка через агрегатор ApiShip. Управление тарифами и подключениями — в личном кабинете ApiShip.';
    }

    public static function canHasProfiles(): bool
    {
        return true;
    }

    /**
     * Профили заводятся вручную в админке (гибридная схема, ADR-004): каждый профиль —
     * это ApiShipProfile с CONFIG.MAIN.PROVIDER_KEY/TARIFF_ID. Списка "готовых" профилей
     * для автосоздания здесь нет — они видны/создаются через getProfilesList() ниже.
     */
    public function getProfilesList(): array
    {
        return [
            [
                'CLASS_NAME' => ApiShipProfile::class,
                'PARENT_ID'  => $this->getId(),
            ],
        ];
    }

    /**
     * Минимальная конфигурация: providerKey/tariffId/providerConnectId опциональны —
     * если не заданы, расчёт делается по всем подключённым ТК ApiShip (см. CalculatorService).
     */
    public function getConfigStructure(): array
    {
        return [
            'MAIN' => [
                'NAME'  => Loc::getMessage('BEERALEX_APISHIP_CONFIG_MAIN') ?: 'Основные настройки',
                'ITEMS' => [
                    'PROVIDER_KEY' => [
                        'TYPE'    => 'STRING',
                        'NAME'    => Loc::getMessage('BEERALEX_APISHIP_CONFIG_PROVIDER_KEY') ?: 'Ключ ТК в ApiShip (providerKey)',
                        'REQUIRED' => 'N',
                    ],
                    'TARIFF_ID' => [
                        'TYPE'    => 'STRING',
                        'NAME'    => Loc::getMessage('BEERALEX_APISHIP_CONFIG_TARIFF_ID') ?: 'ID тарифа ApiShip (tariffId)',
                        'REQUIRED' => 'N',
                    ],
                    'PROVIDER_CONNECT_ID' => [
                        'TYPE'    => 'STRING',
                        'NAME'    => Loc::getMessage('BEERALEX_APISHIP_CONFIG_PROVIDER_CONNECT_ID') ?: 'ID подключения ApiShip (providerConnectId)',
                        'REQUIRED' => 'N',
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
        } catch (ApiShipException $e) {
            $result->addError(new \Bitrix\Main\Error($e->getMessage(), 'APISHIP_CALCULATE'));
            return $result;
        }

        $tariff = $this->pickTariff($tariffs);
        if ($tariff === null) {
            $result->addError(new \Bitrix\Main\Error(
                Loc::getMessage('BEERALEX_APISHIP_NO_TARIFF') ?: 'Не удалось рассчитать стоимость доставки',
                'APISHIP_NO_TARIFF'
            ));
            return $result;
        }

        $result->setDeliveryPrice($tariff->getDeliveryCost());
        $result->setPeriodFrom($tariff->getDaysMin());
        $result->setPeriodTo($tariff->getDaysMax());
        $result->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);

        return $result;
    }

    /**
     * Выбирает тариф согласно CONFIG (PROVIDER_KEY/TARIFF_ID), либо самый дешёвый из всех.
     */
    protected function pickTariff(array $tariffs): ?TariffDto
    {
        $config = $this->getConfigValues()['MAIN'] ?? [];
        $providerKey = (string)($config['PROVIDER_KEY'] ?? '');
        $tariffId = isset($config['TARIFF_ID']) && $config['TARIFF_ID'] !== '' ? (int)$config['TARIFF_ID'] : null;

        if ($providerKey !== '' || $tariffId !== null) {
            foreach ($tariffs as $tariff) {
                if ($providerKey !== '' && $tariff->getProviderKey() !== $providerKey) {
                    continue;
                }
                if ($tariffId !== null && $tariff->getTariffId() !== $tariffId) {
                    continue;
                }
                return $tariff;
            }
            return null;
        }

        $cheapest = null;
        foreach ($tariffs as $tariff) {
            if ($cheapest === null || $tariff->getDeliveryCost() < $cheapest->getDeliveryCost()) {
                $cheapest = $tariff;
            }
        }

        return $cheapest;
    }

    /**
     * Собирает данные для расчёта из Shipment. Адрес отправителя — из настроек модуля/точки
     * отгрузки (TODO: единый источник склада, сейчас placeholder "from" пуст — заполняется
     * при интеграции с конкретным проектом).
     */
    protected function buildCalculationRequest(Shipment $shipment): CalculationRequestDto
    {
        $order = $shipment->getOrder();
        $to = [];

        if ($order !== null) {
            $properties = $order->getPropertyCollection();
            $city = $properties->getItemByOrderPropertyCode('CITY');
            $address = $properties->getItemByOrderPropertyCode('ADDRESS');

            $to = array_filter([
                'city'          => $city?->getValue(),
                'addressString' => $address?->getValue(),
                'countryCode'   => 'RU',
            ], static fn($v) => $v !== null && $v !== '');
        }

        return CalculationRequestDto::make([
            'from'   => [],
            'to'     => $to,
            'places' => [[
                'weight' => (int)($shipment->getWeight() ?: 1000),
                'width'  => 20,
                'height' => 20,
                'length' => 20,
            ]],
        ]);
    }

    protected function getCalculatorService(): CalculatorServiceContract
    {
        return \service(CalculatorServiceContract::class);
    }
}
