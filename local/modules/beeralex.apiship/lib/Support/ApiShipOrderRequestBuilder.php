<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Apiship\Entity\Request\CreateOrderRequest;
use Apiship\Entity\Request\Part\Orders\ExtraParam;
use Apiship\Entity\Request\Part\Orders\Order as OrderPart;
use Apiship\Entity\Request\Part\Orders\Place;
use Apiship\Entity\Request\Part\Orders\Recipient;
use Apiship\Entity\Request\Part\Orders\ReturnAddress;
use Apiship\Entity\Request\Part\Orders\Sender;
use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Sdk\Entity\Request\Part\Cost;
use Beeralex\Apiship\Sdk\Entity\Request\Part\Item;
use Beeralex\Apiship\Support\Concerns\RequestHydrator;

/**
 * Строит SDK-запрос {@see CreateOrderRequest} из {@see CreateOrderDto}. Без обращений к Bitrix.
 */
final class OrderRequestBuilder
{
    use RequestHydrator;

    private const ORDER_FIELDS = [
        'clientNumber',
        'providerKey',
        'providerConnectId',
        'tariffId',
        'pickupType',
        'deliveryType',
        'weight',
        'description',
        'barcode',
        'height',
        'length',
        'width',
        'pointInId',
        'pointOutId',
        'pickupDate',
        'pickupTimeStart',
        'pickupTimeEnd',
        'deliveryDate',
        'deliveryTimeStart',
        'deliveryTimeEnd',
    ];

    public function build(CreateOrderDto $dto): CreateOrderRequest
    {
        $orderData = [];
        foreach (self::ORDER_FIELDS as $field) {
            $orderData[$field] = $dto->$field;
        }

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
