<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;

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
                static::class        => str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__),
                ApiShipProfile::class => str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__ . '/ApiShipProfile.php'),
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
        $list = [];
        try {
            foreach (\service(ConnectionServiceContract::class)->getConnections() as $conn) {
                $id          = (string)($conn['id'] ?? '');
                $providerKey = (string)($conn['providerKey'] ?? '');
                $name        = (string)($conn['name'] ?? $providerKey);

                if ($id !== '' && $providerKey !== '') {
                    $list["{$id}|{$providerKey}"] = "{$name} ({$providerKey})";
                }
            }
        } catch (\Throwable $e) {
        }
        return $list;
    }

    public static function getChildrenClassNames(): array
    {
        return [ApiShipProfile::class];
    }

    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();
        $result->addError(new \Bitrix\Main\Error(
            Loc::getMessage('BEERALEX_APISHIP_PARENT_CALC') ?: 'Расчёт выполняется в профилях доставки ApiShip',
            'APISHIP_PARENT_CALC'
        ));
        return $result;
    }
}
