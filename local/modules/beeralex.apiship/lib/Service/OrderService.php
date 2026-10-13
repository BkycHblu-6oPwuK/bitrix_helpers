<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Request\CreateOrderRequest;
use Apiship\Entity\Request\Part\Orders\ExtraParam;
use Apiship\Entity\Request\Part\Orders\Order as OrderPart;
use Apiship\Entity\Request\Part\Orders\Place;
use Apiship\Entity\Request\Part\Orders\Recipient;
use Apiship\Entity\Request\Part\Orders\ReturnAddress;
use Apiship\Entity\Request\Part\Orders\Sender;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Sdk\Entity\Request\Part\Cost;
use Beeralex\Apiship\Sdk\Entity\Request\Part\Item;
use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Dto\OrderResultDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Service\Support\RequestHydrator;
use Bitrix\Main\SystemException;

/**
 * Создание и управление заказами ApiShip.
 *
 * createForBitrixOrder() — точка входа для события смены статуса заказа Bitrix
 * (см. docs/DECISIONS.md ADR-005): собирает CreateOrderDto через
 * ApiShipOrderRequestBuilder и создаёт заказ асинхронно (ADR-006).
 */
final class OrderService implements OrderServiceContract
{
    use RequestHydrator;

    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Options $options
    ) {}

    /**
     * @throws ApiShipException в случае ошибки при создании заказа в ApiShip.
     * @throws SystemException в случае ошибки при сохранении заказа в системе.
     */
    public function create(CreateOrderDto $dto): OrderResultDto
    {
        $request = $this->buildRequest($dto);

        try {
            $response = $this->clientFactory->getClient()->orders()->create($request);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $result = OrderResultDto::make([
            'orderId' => $response->getOrderId(),
            'created' => $response->getCreated(),
        ]);

        if ($dto->bitrixOrderId > 0) {
            \service(OrderRepository::class)->saveCreated($dto, $result, (string)$request->asJson());
        }

        return $result;
    }

    public function cancel(int $apiShipOrderId): bool
    {
        try {
            $this->clientFactory->getClient()->orders()->cancel($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
        return true;
    }

    public function delete(int $apiShipOrderId): bool
    {
        try {
            $this->clientFactory->getClient()->orders()->delete($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
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

    /**
     * Создать заказ ApiShip для существующего заказа Bitrix. Связь и снимок запроса пишет create().
     */
    public function createForBitrixOrder(int $bitrixOrderId): OrderResultDto
    {
        $builder = \service(\Beeralex\Apiship\Builder\ApiShipOrderRequestBuilder::class);
        $dto = $builder->buildFromBitrixOrder($bitrixOrderId);

        return $this->create($dto);
    }

    private function buildRequest(CreateOrderDto $dto): CreateOrderRequest
    {
        $orderFields = [
            'clientNumber', 'providerKey', 'providerConnectId', 'tariffId', 'pickupType', 'deliveryType',
            'weight', 'description', 'barcode', 'height', 'length', 'width', 'pointInId', 'pointOutId',
            'pickupDate', 'pickupTimeStart', 'pickupTimeEnd', 'deliveryDate', 'deliveryTimeStart', 'deliveryTimeEnd',
        ];
        $orderData = [];
        foreach ($orderFields as $field) {
            $orderData[$field] = $dto->$field;
        }
        $orderData['providerConnectId'] ??= $this->options->defaultProviderConnectId ?: null;

        $request = (new CreateOrderRequest())
            ->setOrder($this->hydrate(new OrderPart(), $orderData))
            ->setCost($this->hydrate(new Cost(), array_filter($dto->cost, static fn($v) => $v !== [])))
            ->setSender($this->hydrate(new Sender(), $dto->sender))
            ->setRecipient($this->hydrate(new Recipient(), $dto->recipient));

        if ($dto->returnAddress !== []) {
            $request->setReturnAddress($this->hydrate(new ReturnAddress(), $dto->returnAddress));
        }

        foreach ($dto->places as $placeData) {
            $place = $this->hydrate(new Place(), array_diff_key($placeData, ['items' => true]));
            foreach ($placeData['items'] as $itemData) {
                $place->addItem($this->hydrate(new Item(), $itemData));
            }
            $request->addPlace($place);
        }

        foreach ($dto->extraParams as $param) {
            $request->addExtraParam($this->hydrate(new ExtraParam(), $param));
        }

        return $request;
    }
}
