<?php

namespace Beeralex\Catalog\Checkout;

use Bitrix\Sale\Order;
use Illuminate\Support\Collection;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Catalog\Checkout\Delivery\BaseDelivery;
use Beeralex\Catalog\Checkout\Dto\DeliveryDTO;
use Beeralex\Catalog\Checkout\Dto\DeliveryiesDTO;
use Beeralex\Catalog\Helper\OrderHelper;
use Beeralex\Catalog\Repository\StoreRepository;

class DeliveriesBuilder
{
    const TRANSPORT_DELIVERIES = [
        'eslogistic:sdek_term',
        'eslogistic:kit_term',
        'eslogistic:pecom_term',
        'eslogistic:delline_term',
        'eslogistic:kit_door',
        'eslogistic:sdek_door',
        'eslogistic:delline_door',
        'eslogistic:pecom_door',
    ];
    const DOOR_DELIVERIES = [
        'eslogistic:kit_door',
        'eslogistic:sdek_door',
        'eslogistic:delline_door',
        'eslogistic:pecom_door',
    ];
    const DISTANCE_PRICE_SERVICE_CODE = 'DISTANCE_PRICE_SERVICE';
    const OWN_DELIVERY_CODE = 'own_delivery';
    /**
     * @var \Bitrix\Sale\Order
     */
    private $order;

    /**
     * @var Collection<BaseDelivery>
     */
    private $deliveries;

    /**
     * @var \Bitrix\Sale\Delivery\Services\Base[]
     */
    private $deliveryHandlers;

    /**
     * @var \Bitrix\Sale\PropertyValueCollectionBase
     */
    private $orderProperties;

    /**
     * @var null|\Bitrix\Sale\Shipment
     */
    private $shipment;

    /**
     * @var int
     */
    private $storeSelectedId;

    public function __construct(
        Collection $deliveries,
        array $deliveryServices,
        Order $order
    ) {
        $this->order = $order;
        $this->deliveryHandlers = $deliveryServices;
        $this->orderProperties = $order->getPropertyCollection();
        $this->shipment = collect($order->getShipmentCollection()->getNotSystemItems())->first();
        $this->deliveries = $deliveries->mapWithKeys(fn($delivery) => [$delivery['ID'] => new BaseDelivery($delivery, $this->deliveryHandlers[$delivery['ID']])]);
        $this->storeSelectedId = $this->shipment?->getStoreId() ?? 0;
    }

    public function buildDeliveriesDTO(): DeliveryiesDTO
    {
        $dto = new DeliveryiesDTO;
        $this->buildLocation($dto);

        if ($this->haveAvailableDeliveries()) {
            $dto->deliveries = $this->deliveries->map(fn(BaseDelivery $delivery) => $this->buildDeliveryDTO($delivery))->toArray();
        } elseif (!$dto->location) {
            $dto->message = 'Мы не доставляем товар в ваш населенный пункт';
        }

        $dto->storeSelectedId = $this->storeSelectedId;
        $dto->selectedId = $this->shipment?->getDeliveryId() ?? 0;
        $dto->minDeliveryPrice = $this->getMinPrice();
        $dto->minDeliveryPriceFormatted = PriceHelper::format($dto->minDeliveryPrice);
        return $dto;
    }

    private function buildDeliveryDTO(BaseDelivery $delivery): DeliveryDTO
    {
        $dto = new DeliveryDTO();
        $dto->id = $delivery->getId();
        $dto->code = $delivery->getCode();
        $dto->name = $delivery->getName();
        $dto->ownName = $delivery->getOwnName();
        $dto->description = $delivery->getDescription();
        $dto->currency = $this->order->getCurrency();
        $dto->sort = $delivery->getSort();
        $dto->logotip = $delivery->getLogotip();
        $dto->price = $delivery->getPrice();
        $dto->isSelected = $delivery->isSelected();
        $dto->extraServices = $dto->isSelected ? $this->getExtraServices($delivery->getExtraServices()) : [];
        $dto->isStoreDelivery = $delivery->isStoreDelivery();
        $dto->isTransport = in_array($delivery->getCode(), self::TRANSPORT_DELIVERIES);
        $dto->isDoor = in_array($delivery->getCode(), self::DOOR_DELIVERIES);
        $dto->isOwnDelivery = $delivery->getCode() === self::OWN_DELIVERY_CODE;
        if ($dto->isStoreDelivery) {
            $dto->storeList = $this->getStoreData($delivery);
        }
        return $dto;
    }

    private function buildLocation(DeliveryiesDTO $dto)
    {
        $props = OrderHelper::getPropertyValues($this->orderProperties);
        $dto->location = $props['LOCATION'] ?? '';
        $dto->city = $props['CITY'] ?? '';
        $dto->address = $props['ADDRESS'] ?: $dto->city;
        $dto->postCode = $props['ZIP'] ?? '';
        $dto->selectedPvz = $props['ESHOPLOGISTIC_PVZ'] ?? '';
        $dto->coordinates = $props['COORDINATES'] ? explode(',', $props['COORDINATES'], 2) : [];
        $dto->completionDate = $props['COMPLETION_DATE'] ?? '';
        $dto->distance = (float)$props['DISTANCE'] ?? 0;
        $dto->duration = (float)$props['DURATION'] ?? 0;
    }

    /**
     * @return bool
     */
    public function haveAvailableDeliveries(): bool
    {
        return $this->deliveries
            ->contains(function (BaseDelivery $delivery) {
                return $delivery->isAvailable();
            });
    }

    private function getExtraServices(array $extraServices): array
    {
        $extraServicesFormatted = [];
        /** @var \Bitrix\Sale\Delivery\ExtraServices\Base $extraService */
        foreach ($extraServices as $extraServiceId => $extraService) {
            if ($extraService->canUserEditValue()) {
                $values = [];
                $price = (float)$extraService->getPriceShipment($this->shipment);
                $params = $extraService->getParams();
                $selectedValue = $extraService->getValue();
                foreach ($params['PRICES'] ?? [] as $id => $item) {
                    $priceItem = (float)$item['PRICE'];
                    if ($id == $selectedValue) {
                        $selectedValue = (int)$selectedValue;
                        $price = $priceItem;
                    }
                    $values[] = [
                        'id' => $id,
                        'title' => $item['TITLE'],
                        'price' => $priceItem,
                        'priceFormatted' => PriceHelper::format($priceItem)
                    ];
                }
                $code = $extraService->getCode();
                $isPriceService = $code === static::DISTANCE_PRICE_SERVICE_CODE;

                $extraServicesFormatted[] = [
                    'id' => $extraServiceId,
                    'code' => $extraService->getCode(),
                    'isPriceService' => $isPriceService,
                    'title' => $extraService->getName(),
                    'value' => $selectedValue,
                    'values' => $values,
                    'price' => $price,
                    'priceFormatted' => PriceHelper::format($price),
                    'description' => $extraService->getDescription(),
                    'type' => $params['TYPE'] ?? ''
                ];
            }
        }
        return $extraServicesFormatted;
    }

    private function getStoreData(BaseDelivery $delivery): array
    {
        $isSelected = $delivery->isSelected();
        if (!$isSelected) {
            return [];
        }

        $storeData = (new StoreRepository)->getPickPoints($delivery->getStoreList());
        if (!$this->storeSelectedId && $storeSelected = $storeData[0]) {
            $this->storeSelectedId = $storeSelected->id;
            $this->shipment->setStoreId($storeSelected->id);
        }
        return $storeData;
    }

    private function getMinPrice()
    {
        return $this->deliveries->map(fn(BaseDelivery $delivery) => $delivery->getPrice())->min();
    }
}
