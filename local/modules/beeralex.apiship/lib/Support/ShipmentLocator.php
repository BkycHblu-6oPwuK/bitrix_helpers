<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

/**
 * Поиск отгрузки, оформленной службой доставки ApiShip, внутри заказа Bitrix.
 *
 * Логика вынесена сюда, чтобы её переиспользовали и билдер запроса (ApiShipOrderRequestBuilder),
 * и маппер статусов (StatusMapper): системная отгрузка пропускается, обработчик доставки
 * сверяется с ApiShipHandler/ApiShipProfile.
 */
final class ShipmentLocator
{
    public static function findApiShipShipment(Order $order): ?Shipment
    {
        $handlerClass = 'Beeralex\\Apiship\\Delivery\\ApiShipHandler';
        $profileClass = 'Beeralex\\Apiship\\Delivery\\ApiShipProfile';

        foreach ($order->getShipmentCollection() as $shipment) {
            if ($shipment->isSystem()) {
                continue;
            }
            $delivery = Manager::getObjectById($shipment->getDeliveryId());
            if ($delivery !== null && (is_a($delivery, $handlerClass) || is_a($delivery, $profileClass))) {
                return $shipment;
            }
        }

        return null;
    }
}
