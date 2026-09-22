<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Builder;

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Exception\ApiShipValidationException;
use Beeralex\Apiship\Options;

/**
 * Собирает CreateOrderDto из заказа Bitrix (Order/Shipment/PropertyValue/BasketItem).
 *
 * Коды свойств заказа (PHONE/EMAIL/FIO/NAME/LAST_NAME/CITY/ADDRESS/LOCATION) — стандартные
 * для проекта (см. local/components/beeralex/sale.order.ajax/class.php). При иной схеме
 * свойств переопределите getPropertyValue() или сконфигурируйте маппинг отдельно.
 */
final class ApiShipOrderRequestBuilder
{
    public function __construct(
        private readonly Options $options
    ) {}

    public function buildFromBitrixOrder(int $bitrixOrderId): CreateOrderDto
    {
        $order = Order::load($bitrixOrderId);
        if ($order === null) {
            throw new ApiShipValidationException("Заказ #{$bitrixOrderId} не найден");
        }

        $shipment = $this->findApiShipShipment($order);
        if ($shipment === null) {
            throw new ApiShipValidationException("В заказе #{$bitrixOrderId} нет доставки ApiShip");
        }

        $profileConfig = $this->getProfileConfig($shipment);

        return CreateOrderDto::make([
            'bitrixOrderId'      => $order->getId(),
            'providerKey'        => (string)($profileConfig['PROVIDER_KEY'] ?? ''),
            'tariffId'           => isset($profileConfig['TARIFF_ID']) ? (int)$profileConfig['TARIFF_ID'] : null,
            'providerConnectId'  => isset($profileConfig['PROVIDER_CONNECT_ID'])
                ? (int)$profileConfig['PROVIDER_CONNECT_ID']
                : $this->options->defaultProviderConnectId,
            'clientNumber'       => (string)$order->getId(),
            'sender'             => $this->buildSender(),
            'recipient'          => $this->buildRecipient($order),
            'cost'               => $this->buildCost($order, $shipment),
            'items'              => $this->buildItems($shipment),
        ]);
    }

    private function findApiShipShipment(Order $order): ?Shipment
    {
        // Строковые FQCN: классы обработчика/профиля появляются в Фазе 3, но метод
        // должен корректно работать (и быть валидным для статического анализа) уже сейчас.
        $handlerClass = 'Beeralex\\Apiship\\Delivery\\ApiShipHandler';
        $profileClass = 'Beeralex\\Apiship\\Delivery\\ApiShipProfile';

        foreach ($order->getShipmentCollection() as $shipment) {
            if ($shipment->isSystem()) {
                continue;
            }
            $delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());
            if ($delivery !== null && (is_a($delivery, $handlerClass) || is_a($delivery, $profileClass))) {
                return $shipment;
            }
        }

        return null;
    }

    /**
     * Настройки конкретного профиля/службы доставки (CONFIG.MAIN), заданные в админке.
     *
     * @return array<string,mixed>
     */
    private function getProfileConfig(Shipment $shipment): array
    {
        $delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());
        if (!$delivery) {
            return [];
        }
        $config = $delivery->getConfigValues();
        return (array)($config['MAIN'] ?? $config ?? []);
    }

    /**
     * Отправитель — реквизиты компании из настроек модуля/маркетплейса.
     * TODO: перенести в отдельные настройки модуля, если потребуется несколько складов.
     *
     * @return array<string,mixed>
     */
    private function buildSender(): array
    {
        return [];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildRecipient(Order $order): array
    {
        $properties = $order->getPropertyCollection();

        $name = $this->getPropertyValue($properties, 'FIO')
            ?: trim(($this->getPropertyValue($properties, 'NAME') ?? '') . ' ' . ($this->getPropertyValue($properties, 'LAST_NAME') ?? ''));

        return array_filter([
            'contactName'   => $name ?: null,
            'phone'         => $this->getPropertyValue($properties, 'PHONE'),
            'email'         => $this->getPropertyValue($properties, 'EMAIL'),
            'city'          => $this->getPropertyValue($properties, 'CITY'),
            'addressString' => $this->getPropertyValue($properties, 'ADDRESS'),
            'countryCode'   => 'RU',
        ], static fn($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<string,mixed>
     */
    private function buildCost(Order $order, Shipment $shipment): array
    {
        return [
            'assessedCost' => (float)$order->getPrice(),
            'deliveryCost' => (float)$shipment->getPrice(),
            'codCost'      => 0,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function buildItems(Shipment $shipment): array
    {
        $items = [];

        foreach ($shipment->getShipmentItemCollection() as $shipmentItem) {
            /** @var BasketItem|null $basketItem */
            $basketItem = $shipmentItem->getBasketItem();
            if ($basketItem === null) {
                continue;
            }

            $items[] = [
                'description'  => (string)$basketItem->getField('NAME'),
                'articul'      => (string)$basketItem->getField('PRODUCT_XML_ID'),
                'quantity'     => (int)$shipmentItem->getQuantity(),
                'cost'         => (float)$basketItem->getPrice(),
                'assessedCost' => (float)$basketItem->getPrice(),
                'weight'       => (int)$basketItem->getWeight(),
            ];
        }

        return $items;
    }

    /**
     * @return string|null
     */
    private function getPropertyValue($propertyCollection, string $code): ?string
    {
        $property = $propertyCollection->getItemByOrderPropertyCode($code);
        if ($property === null) {
            return null;
        }
        $value = $property->getValue();
        return $value !== null && $value !== '' ? (string)$value : null;
    }
}
