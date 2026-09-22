<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Request\CreateOrderRequest;
use Apiship\Entity\Request\Part\Orders\Cost;
use Apiship\Entity\Request\Part\Orders\Item;
use Apiship\Entity\Request\Part\Orders\Order as OrderPart;
use Apiship\Entity\Request\Part\Orders\Recipient;
use Apiship\Entity\Request\Part\Orders\Sender;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Dto\OrderResultDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Service\Support\RequestHydrator;

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

    public function create(CreateOrderDto $dto): OrderResultDto
    {
        $request = $this->buildRequest($dto);

        try {
            $response = $this->clientFactory->getClient()->orders()->create($request);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return OrderResultDto::make([
            'orderId' => $response->getOrderId(),
            'created' => $response->getCreated(),
        ]);
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
     * Создать заказ ApiShip для существующего заказа Bitrix (или вернуть уже созданный).
     * Связь orderId<->apiShipOrderId хранится в Repository\OrderRepository (Фаза 4).
     */
    public function createForBitrixOrder(int $bitrixOrderId): OrderResultDto
    {
        $builder = \service(\Beeralex\Apiship\Builder\ApiShipOrderRequestBuilder::class);
        $dto = $builder->buildFromBitrixOrder($bitrixOrderId);

        return $this->create($dto);
    }

    private function buildRequest(CreateOrderDto $dto): CreateOrderRequest
    {
        $orderPart = $this->hydrate(new OrderPart(), array_filter([
            'providerKey'        => $dto->getProviderKey(),
            'providerConnectId'  => $dto->getProviderConnectId() ?: ($this->options->defaultProviderConnectId ?: null),
            'tariffId'           => $dto->getTariffId(),
            'clientNumber'       => $dto->getClientNumber(),
        ], static fn($v) => $v !== null));

        $cost = $this->hydrate(new Cost(), $dto->getCost());
        $sender = $this->hydrate(new Sender(), $dto->getSender());
        $recipient = $this->hydrate(new Recipient(), $dto->getRecipient());

        $request = (new CreateOrderRequest())
            ->setOrder($orderPart)
            ->setCost($cost)
            ->setSender($sender)
            ->setRecipient($recipient);

        foreach ($dto->getItems() as $itemData) {
            $request->addItem($this->hydrate(new Item(), $itemData));
        }

        return $request;
    }
}
