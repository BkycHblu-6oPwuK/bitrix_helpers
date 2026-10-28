<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Builder;

use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Exception\ApiShipValidationException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\ShipmentLocator;
use Beeralex\Apiship\Delivery\ApiShipHandler;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;

/**
 * Собирает CreateOrderDto из заказа Bitrix (Order/Shipment/Payment/PropertyValue/BasketItem).
 *
 * Коды свойств заказа: PHONE/EMAIL/FIO/NAME/LAST_NAME/CITY/ADDRESS/ZIP/PICKUP_POINT_ID —
 * стандартные для проекта (см. local/components/beeralex/sale.order.ajax/class.php).
 * PICKUP_POINT_ID трактуется как ID пункта выдачи ApiShip (pointOutId).
 * Отправитель и продавец позиций берутся из настроек модуля (Options::$sender).
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

        $shipment = ShipmentLocator::findApiShipShipment($order);
        if ($shipment === null) {
            throw new ApiShipValidationException("В заказе #{$bitrixOrderId} нет доставки ApiShip");
        }

        $config = $this->getProfileConfig($shipment);

        // Читаем CONNECTION_KEY = "{connectId}|{providerKey}" (новый формат) или PROVIDER_KEY (старый)
        [$connectId, $providerKey] = ApiShipHandler::parseConnectionKey($config);
        $tariffId = (int)($config['TARIFF_ID'] ?? 0);

        if ($providerKey === '' || $tariffId <= 0) {
            throw new ApiShipValidationException(
                "В службе доставки заказа #{$bitrixOrderId} не заданы PROVIDER_KEY/TARIFF_ID"
            );
        }

        // Определяем COD один раз: items.cost = 0 для предоплаты, реальная цена для наложенного платежа
        $isCod = false;
        foreach ($order->getPaymentCollection() as $payment) {
            if (!$payment->isPaid() && $payment->getPaySystem()?->isCash()) {
                $isCod = true;
                break;
            }
        }

        $items = $this->buildItems($shipment, $isCod);
        if ($items === []) {
            throw new ApiShipValidationException("В отгрузке заказа #{$bitrixOrderId} нет товаров");
        }

        $properties = $order->getPropertyCollection();
        $pointOutId = (int)$this->getPropertyValue($properties, $this->options->propPickupPointId);
        $pointInId = (int)($config['POINT_IN_ID'] ?? 0);
        $pickupType = (int)($config['PICKUP_TYPE'] ?? 0) ?: ($pointInId > 0 ? 2 : 1);

        // providerConnectId: из CONNECTION_KEY или из явного PROVIDER_CONNECT_ID (старый формат)
        $providerConnectId = $connectId !== ''
            ? $connectId
            : (string)($config['PROVIDER_CONNECT_ID'] ?? '');
        $providerConnectId = $providerConnectId ?: ((string)$this->options->defaultProviderConnectId ?: null);

        $weight = 0;
        foreach ($items as $item) {
            $weight += $item['weight'] * $item['quantity'];
        }
        $weight = $weight ?: $this->options->defaultWeight;

        return CreateOrderDto::make([
            'bitrixOrderId'     => $order->getId(),
            'clientNumber'      => (string)$order->getField('ACCOUNT_NUMBER') ?: (string)$order->getId(),
            'providerKey'       => $providerKey,
            'tariffId'          => $tariffId,
            'providerConnectId' => $providerConnectId,
            'pickupType'        => $pickupType,
            'deliveryType'      => $pointOutId > 0 ? 2 : 1,
            'pointInId'         => $pointInId > 0 ? $pointInId : null,
            'pointOutId'        => $pointOutId > 0 ? $pointOutId : null,
            'description'       => (string)$order->getField('USER_DESCRIPTION'),
            'weight'            => $weight,
            'cost'              => $this->buildCost($order, $shipment, $items),
            'sender'            => $this->options->sender,
            'recipient'         => $this->buildRecipient($properties, $order->getUserId()),
            'places'            => [[
                'placeNumber' => '1',
                'weight'      => $weight,
                'items'       => $items,
            ]],
        ]);
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
     * @return array<string,mixed>
     */
    private function buildRecipient(PropertyValueCollectionBase $properties, int $userId): array
    {
        $name = $this->getPropertyValue($properties, $this->options->propFio)
            ?: trim(
                ($this->getPropertyValue($properties, 'NAME') ?? '')
                    . ' '
                    . ($this->getPropertyValue($properties, 'LAST_NAME') ?? '')
            );
            
        if(empty($name)) {
            /** @var \CUser $USER */
            global $USER;
            $userData = $USER->GetByID($userId)->Fetch();
            $name = trim(($userData['NAME'] ?? '') . ' ' . ($userData['LAST_NAME'] ?? ''));
        }

        return [
            'contactName'   => $name,
            'phone'         => $this->getPropertyValue($properties, $this->options->propPhone),
            'email'         => $this->getPropertyValue($properties, 'EMAIL'),
            'city'          => $this->getPropertyValue($properties, $this->options->propCity),
            'cityGuid'      => $this->getPropertyValue($properties, $this->options->propCityGuid),
            'postIndex'     => $this->getPropertyValue($properties, 'ZIP'),
            'addressString' => $this->getPropertyValue($properties, $this->options->propAddress),
            'countryCode'   => 'RU',
        ];
    }

    /**
     * Наложенный платёж — сумма неоплаченных платежей наличной платёжной системы; доставку тогда платит получатель.
     *
     * @param array<int,array<string,mixed>> $items
     * @return array<string,mixed>
     */
    private function buildCost(Order $order, Shipment $shipment, array $items): array
    {
        $assessedCost = 0.0;
        foreach ($items as $item) {
            $assessedCost += $item['assessedCost'] * $item['quantity'];
        }

        $codCost = 0.0;
        foreach ($order->getPaymentCollection() as $payment) {
            if (!$payment->isPaid() && $payment->getPaySystem()?->isCash()) {
                $codCost += (float)$payment->getSum();
            }
        }

        $isCod = $codCost > 0;

        return [
            'assessedCost'               => $assessedCost,
            'codCost'                    => $codCost,
            'deliveryCost'               => $isCod ? (float)$shipment->getPrice() : null,
            'deliveryCostVat' => $this->normalizeVat(
                $shipment->getDelivery()?->getVatId() > 0 ? (float)$shipment->getVatRate() : null
            ),
            'isDeliveryPayedByRecipient' => $isCod ? 1 : 0,
            'paymentMethod'              => $isCod ? 1 : null,
        ];
    }

    /**
     * @param bool $isCod items.cost = реальная цена (COD) или 0 (предоплата, страховка через assessedCost)
     * @return array<int,array<string,mixed>>
     */
    private function buildItems(Shipment $shipment, bool $isCod): array
    {
        $seller = $this->options->sender;
        $items = [];

        foreach ($shipment->getShipmentItemCollection() as $shipmentItem) {
            /** @var BasketItem|null $basketItem */
            $basketItem = $shipmentItem->getBasketItem();
            if ($basketItem === null) {
                continue;
            }

            $dimensions = $this->getDimensionsCm($basketItem);
            $vatRate = $basketItem->getVatRate();

            $items[] = [
                'description'  => (string)$basketItem->getField('NAME'),
                'articul'      => (string)$basketItem->getField('PRODUCT_XML_ID'),
                'quantity'     => (int)$shipmentItem->getQuantity(),
                'cost'         => $isCod ? (float)$basketItem->getPrice() : 0.0,
                'assessedCost' => (float)$basketItem->getPrice(),
                'costVat'      => $this->normalizeVat($vatRate === null ? null : (float)$vatRate),
                'weight'       => (int)$basketItem->getWeight(),
                'height'       => $dimensions['HEIGHT'],
                'length'       => $dimensions['LENGTH'],
                'width'        => $dimensions['WIDTH'],
                'url'          => $this->buildItemUrl((string)$basketItem->getField('DETAIL_PAGE_URL')),
                'companyName'  => $seller['companyName'] ?? null,
                'companyInn'   => $seller['companyInn'] ?? null,
                'companyPhone' => $seller['phone'] ?? null,
            ];
        }

        return $items;
    }

    /**
     * Ставка Bitrix (доля, 0.2) -> процент ApiShip. null -> -1 (без НДС). Ставка вне набора -> -1.
     */
    private function normalizeVat(?float $rate): int
    {
        return $rate === null ? -1 : (int)round($rate * 100);
    }
    /**
     * Габариты позиции Bitrix (мм) -> см. Нет данных — null.
     *
     * @return array{WIDTH:?int,HEIGHT:?int,LENGTH:?int}
     */
    private function getDimensionsCm(BasketItem $basketItem): array
    {
        $raw = $basketItem->getField('DIMENSIONS');
        if (is_string($raw)) {
            $raw = @unserialize($raw, ['allowed_classes' => false]);
        }

        $result = ['WIDTH' => null, 'HEIGHT' => null, 'LENGTH' => null];
        foreach (array_keys($result) as $key) {
            $mm = is_array($raw) ? (float)($raw[$key] ?? 0) : 0.0;
            $result[$key] = $mm > 0 ? (int)ceil($mm / 10) : null;
        }

        return $result;
    }

    private function buildItemUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $host = (string)Option::get('main', 'server_name', '');

        return $host !== '' ? 'https://' . $host . $path : null;
    }

    private function getPropertyValue(PropertyValueCollectionBase $properties, string $code): ?string
    {
        $property = $properties->getItemByOrderPropertyCode($code);
        if ($property === null) {
            return null;
        }
        $value = $property->getValue();
        return $value !== null && $value !== '' ? (string)$value : null;
    }
}
