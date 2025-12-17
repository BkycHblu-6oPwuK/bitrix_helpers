<?php

namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\Order;
use Illuminate\Support\Collection;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Api\Domain\Checkout\Delivery\BaseDelivery;
use Beeralex\Api\Domain\Checkout\DTO\DeliveryDTO;
use Beeralex\Api\Domain\Checkout\DTO\DeliveryiesDTO;
use Beeralex\Api\Domain\Checkout\DTO\ExtraServiceDTO;
use Beeralex\Api\Domain\Checkout\DTO\StoreDTO;
use Beeralex\Catalog\Service\OrderService;
use Beeralex\Catalog\Repository\StoreRepository;
use Beeralex\Catalog\Service\PriceService;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;

class DeliveriesBuilder
{
    private Order $order;
    /**
     * @var Collection<BaseDelivery>
     */
    private Collection $deliveries;
    /**
     * @var array<int, Base>
     */
    private array $deliveryHandlers;
    private PropertyValueCollectionBase $orderProperties;
    private ?Shipment $shipment;
    private int $storeSelectedId;
    private PriceService $priceService;
    private OrderService $orderService;

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
        $this->priceService = \service(PriceService::class);
    }

    public function buildDeliveriesDTO(): DeliveryiesDTO
    {
        if (!$this->haveAvailableDeliveries()) {
            return DeliveryiesDTO::make([
                'deliveries' => [],
                'selectedId' => 0,
                'storeSelectedId' => 0,
                'minDeliveryPrice' => 0,
                'minDeliveryPriceFormatted' => $this->priceService->format(0),
                'message' => 'Мы не доставляем товар в ваш населенный пункт',
            ]);
        }

        $deliveries = $this->deliveries
            ->map(fn(BaseDelivery $delivery) => $this->buildDeliveryDTO($delivery))
            ->toArray();

        return DeliveryiesDTO::make([
            'deliveries' => $deliveries,
            'selectedId' => $this->shipment?->getDeliveryId() ?? 0,
            'storeSelectedId' => $this->storeSelectedId,
            'minDeliveryPrice' => $this->getMinPrice(),
            'minDeliveryPriceFormatted' => $this->priceService->format($this->getMinPrice()),
            'message' => '',
        ]);
    }

    private function buildLocationData(): array
    {
        $props = $this->orderService->getPropertyValues($this->orderProperties);
        
        return [
            'location' => $props['LOCATION'] ?? '',
            'city' => $props['CITY'] ?? '',
            'address' => $props['ADDRESS'] ?: ($props['CITY'] ?? ''),
            'postCode' => $props['ZIP'] ?? '',
            'selectedPvz' => $props['ESHOPLOGISTIC_PVZ'] ?? '',
            'coordinates' => $props['COORDINATES'] ? explode(',', $props['COORDINATES'], 2) : [],
            'completionDate' => $props['COMPLETION_DATE'] ?? '',
            'distance' => (float)($props['DISTANCE'] ?? 0),
            'duration' => (float)($props['DURATION'] ?? 0),
        ];
    }

    private function buildDeliveryDTO(BaseDelivery $delivery): DeliveryDTO
    {
        $extraServices = $delivery->isSelected() ? $this->getExtraServices($delivery->getExtraServices()) : [];
        $storeList = $delivery->isStoreDelivery() && $delivery->isSelected() 
            ? $this->getStoreData($delivery) 
            : [];

        return DeliveryDTO::make([
            'id' => $delivery->getId(),
            'code' => $delivery->getCode(),
            'name' => $delivery->getName(),
            'ownName' => $delivery->getOwnName(),
            'description' => $delivery->getDescription(),
            'currency' => $this->order->getCurrency(),
            'sort' => $delivery->getSort(),
            'logotip' => $delivery->getLogotip(),
            'price' => $delivery->getPrice(),
            'priceFormatted' => $this->priceService->format($delivery->getPrice()),
            'isSelected' => $delivery->isSelected(),
            'extraServices' => $extraServices,
            'isStoreDelivery' => $delivery->isStoreDelivery(),
            'storeList' => $storeList,
        ]);
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

    /**
     * @return ExtraServiceDTO[]
     */
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
                        'priceFormatted' => $this->priceService->format($priceItem)
                    ];
                }
                
                $code = $extraService->getCode();

                $extraServicesFormatted[] = ExtraServiceDTO::make([
                    'id' => $extraServiceId,
                    'code' => $code,
                    'title' => $extraService->getName(),
                    'value' => $selectedValue,
                    'values' => $values,
                    'price' => $price,
                    'priceFormatted' => $this->priceService->format($price),
                    'description' => $extraService->getDescription(),
                    'type' => $params['TYPE'] ?? ''
                ]);
            }
        }
        return $extraServicesFormatted;
    }

    /**
     * @return StoreDTO[]
     */
    private function getStoreData(BaseDelivery $delivery): array
    {
        if (!$delivery->isSelected()) {
            return [];
        }

        $storeData = (new StoreRepository)->getPickPoints($delivery->getStoreList());
        
        if (!$this->storeSelectedId && isset($storeData[0])) {
            $this->storeSelectedId = $storeData[0]->id;
            $this->shipment->setStoreId($storeData[0]->id);
        }
        
        return $storeData;
    }

    private function getMinPrice()
    {
        return $this->deliveries->map(fn(BaseDelivery $delivery) => $delivery->getPrice())->min();
    }
}
