<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\EventResult;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;

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
        return 'ApiShip';
    }

    public static function getClassDescription(): string
    {
        return 'Единая доставка через агрегатор ApiShip. Управление тарифами и подключениями — в личном кабинете ApiShip.';
    }

    public static function canHasProfiles(): bool
    {
        return true;
    }

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
            'Расчёт выполняется в профилях доставки ApiShip',
            'APISHIP_PARENT_CALC'
        ));
        return $result;
    }
}
