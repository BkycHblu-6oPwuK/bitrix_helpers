<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Support\ClientFactory;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Dto\OrderResultDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Exception\ApiShipValidationException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\CalculatedTariffStore;
use Beeralex\Apiship\Support\CodResolver;
use Beeralex\Apiship\Support\Concerns\ConvertsUnits;
use Beeralex\Apiship\Support\Concerns\ReadsOrderProperties;
use Beeralex\Apiship\Support\OrderItemsMapper;
use Beeralex\Apiship\Support\OrderRequestBuilder;
use Beeralex\Apiship\Support\ProfileConfig;
use Beeralex\Apiship\Support\RecipientReader;
use Beeralex\Apiship\Support\ShipmentLinesReader;
use Beeralex\Apiship\Support\ShipmentLocator;
use Beeralex\Apiship\Support\ShipmentParcelBuilder;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

final class OrderService implements OrderServiceContract
{
    use ConvertsUnits;
    use ReadsOrderProperties;

    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Options $options,
        private readonly OrderRepository $orderRepository,
        private readonly OrderRequestBuilder $requestBuilder,
        private readonly OrderItemsMapper $itemsMapper,
        private readonly RecipientReader $recipientReader,
        private readonly CalculatedTariffStore $tariffStore,
    ) {}

    public function create(CreateOrderDto $dto): OrderResultDto
    {
        $request = $this->requestBuilder->build($dto);

        try {
            $response = $this->clientFactory->getClient()->orders()->create($request);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return OrderResultDto::make(['orderId' => $response->orderId]);
    }

    public function cancel(int $apiShipOrderId): bool
    {
        try {
            $this->clientFactory->getClient()->orders()->cancel($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
        $this->orderRepository->markCancelled($apiShipOrderId);
        return true;
    }

    public function delete(int $apiShipOrderId): bool
    {
        try {
            $this->clientFactory->getClient()->orders()->delete($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
        $this->orderRepository->remove($apiShipOrderId);
        return true;
    }

    public function getInfo(int $apiShipOrderId): array
    {
        try {
            $response = $this->clientFactory->getClient()->orders()->getOrderInfo($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return (array)json_decode((string)$response->getOriginJson(), true);
    }

    public function createForBitrixOrder(int $bitrixOrderId): OrderResultDto
    {
        $existingId = $this->orderRepository->getApiShipOrderId($bitrixOrderId);
        if ($existingId !== null && $existingId > 0) {
            return OrderResultDto::make(['orderId' => $existingId]);
        }

        $order = Order::load($bitrixOrderId)
            ?? throw new ApiShipValidationException("Заказ #{$bitrixOrderId} не найден");
        $shipment = ShipmentLocator::findApiShipShipment($order)
            ?? throw new ApiShipValidationException("В заказе #{$bitrixOrderId} нет доставки ApiShip");

        $result = $this->create($this->collectDto($order, $shipment));

        if ($result->orderId > 0) {
            $this->writeTrackingNumber($order, $shipment, $result->orderId);
        }

        return $result;
    }

    private function collectDto(Order $order, Shipment $shipment): CreateOrderDto
    {
        $orderId = (int)$order->getId();
        $config = ProfileConfig::fromShipment($shipment);
        if ($config->providerKey === '') {
            throw new ApiShipValidationException("В доставке заказа #{$orderId} не выбрано подключение ApiShip");
        }

        // тариф: сохранённый при расчёте, иначе единственный из профиля
        $selected = $this->tariffStore->find($order, (int)$shipment->getDeliveryId());
        $tariffId = $selected['tariffId'] ?? (count($config->tariffIds) === 1 ? $config->tariffIds[0] : 0);
        if ($tariffId <= 0) {
            throw new ApiShipValidationException(
                "Не удалось определить тариф заказа #{$orderId}: расчёт его не сохранил, а в профиле выбрано не ровно одно значение"
            );
        }

        $lines = ShipmentLinesReader::read($shipment);
        if ($lines === []) {
            throw new ApiShipValidationException("В отгрузке заказа #{$orderId} нет товаров");
        }

        $parcel = ShipmentParcelBuilder::build($shipment, [
            'weight' => $this->options->defaultWeight,
            'width'  => $this->options->defaultWidth,
            'height' => $this->options->defaultHeight,
            'length' => $this->options->defaultLength,
        ], $lines);

        $codCost = CodResolver::sum($order);
        $items = $this->itemsMapper->map($lines, $codCost > 0);

        $pointOutId = (int)$this->propertyValue(
            $order->getPropertyCollection(),
            $this->options->propPickupPointId
        );
        $deliveryType = ($selected['deliveryType'] ?? 0) ?: ($config->deliveryType ?: ($pointOutId > 0 ? 2 : 1));
        if ($deliveryType === 2 && $pointOutId <= 0) {
            throw new ApiShipValidationException("Для доставки до ПВЗ в заказе #{$orderId} не выбран пункт выдачи");
        }

        $pickupType = $config->resolvedPickupType();

        return CreateOrderDto::make([
            'bitrixOrderId'     => $orderId,
            'clientNumber'      => (string)$order->getField('ACCOUNT_NUMBER') ?: (string)$orderId,
            'providerKey'       => $config->providerKey,
            'providerConnectId' => $config->connectId,
            'tariffId'          => $tariffId,
            'pickupType'        => $pickupType,
            'deliveryType'      => $deliveryType,
            'pointInId'         => $pickupType === 2 && $config->pointInId > 0 ? $config->pointInId : null,
            'pointOutId'        => $pointOutId > 0 ? $pointOutId : null,
            'description'       => (string)$order->getField('USER_DESCRIPTION'),
            'weight'            => $parcel['weight'],
            'length'            => $parcel['length'],
            'width'             => $parcel['width'],
            'height'            => $parcel['height'],
            'cost'              => $this->buildCost($shipment, $parcel['assessedCost'], $codCost),
            'sender'            => $this->options->sender,
            'recipient'         => $this->recipientReader->read($order),
            'places'            => [[
                'placeNumber' => '1',
                'weight'      => $parcel['weight'],
                'items'       => $items,
            ]],
        ]);
    }

    /** @return array<string,mixed> */
    private function buildCost(Shipment $shipment, float $assessedCost, float $codCost): array
    {
        $isCod = $codCost > 0;
        $vatId = $shipment->getDelivery()?->getVatId();

        return [
            'assessedCost'               => $assessedCost,
            'codCost'                    => $codCost,
            'deliveryCost'               => $isCod ? (float)$shipment->getPrice() : null,
            'deliveryCostVat'            => static::vatToPercent($vatId > 0 ? (float)$shipment->getVatRate() : null),
            'isDeliveryPayedByRecipient' => $isCod ? 1 : 0,
            'paymentMethod'              => $isCod ? 1 : null,
        ];
    }

    private function writeTrackingNumber(Order $order, Shipment $shipment, int $apiShipOrderId): void
    {
        try {
            $shipment->setField('TRACKING_NUMBER', (string)$apiShipOrderId);
            $order->save();
        } catch (\Throwable $e) {
            \AddMessage2Log('ApiShip: не удалось записать TRACKING_NUMBER: ' . $e->getMessage(), 'beeralex.apiship');
        }
    }
}
