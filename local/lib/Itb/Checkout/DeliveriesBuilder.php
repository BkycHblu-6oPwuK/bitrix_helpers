<?php

namespace Itb\Checkout;

use Bitrix\Sale\Order;
use Illuminate\Support\Collection;
use Itb\Catalog\Price;
use Itb\Checkout\Delivery\BaseDelivery;
use Itb\Catalog\Order as CatalogOrder;
use Itb\Catalog\Repository\StoreRepository;

class DeliveriesBuilder
{
    const TRANSPORT_DELIVERIES = [
        'eslogistic:sdek_term',
        'eslogistic:kit_term',
        'eslogistic:pecom_term',
        'eslogistic:delline_term'
    ];
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
        $dto->minDeliveryPriceFormatted = Price::format($dto->minDeliveryPrice);
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
        if ($dto->isStoreDelivery) {
            $dto->storeList = $this->getStoreData($delivery);
        }
        return $dto;
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

    private function buildLocation(DeliveryiesDTO $dto)
    {
        $props = CatalogOrder::getPropertyValues($this->orderProperties);
        $dto->location = $props['LOCATION'] ?? '';
        $dto->city = $props['CITY'] ?? '';
        $dto->address = $props['ADDRESS'] ?? '';
        $dto->postCode = $props['ZIP'] ?? '';
        $dto->selectedPvz = $props['ESHOPLOGISTIC_PVZ'] ?? '';
        $dto->coordinates = $props['COORDINATES'] ? explode(',', $props['COORDINATES'], 2) : [];
        $dto->completionDate = $props['COMPLETION_DATE'] ?? '';
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
                foreach($params['PRICES'] ?? [] as $id => $item) {
                    $priceItem = (float)$item['PRICE'];
                    if($id == $selectedValue) {
                        $selectedValue = (int)$selectedValue;
                        $price = $priceItem;
                    }
                    $values[] = [
                        'id' => $id,
                        'title' => $item['TITLE'],
                        'price' => $priceItem,
                        'priceFormatted' => Price::format($priceItem)
                    ];
                }
                $extraServicesFormatted[] = [
                    'id' => $extraServiceId,
                    'code' => $extraService->getCode(),
                    'title' => $extraService->getName(),
                    'value' => $selectedValue,
                    'values' => $values,
                    'price' => $price,
                    'priceFormatted' => Price::format($price),
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
