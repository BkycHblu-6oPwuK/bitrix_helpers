<?php
namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\Order;
use Illuminate\Support\Collection;
use Beeralex\Api\Domain\Checkout\Delivery\BaseDelivery;
use Beeralex\Api\Domain\Checkout\DTO\DeliveryDTO;
use Beeralex\Api\Domain\Checkout\DTO\DeliveryiesDTO;
use Beeralex\Api\Domain\Checkout\DTO\ExtraServiceDTO;
use Beeralex\Api\Domain\Checkout\DTO\StoreDTO;
use Beeralex\Catalog\Contracts\StoreRepositoryContract;
use Beeralex\Catalog\Service\OrderService;
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
        $this->orderService = \service(OrderService::class);
    }

    public function buildDeliveriesDTO(): DeliveryiesDTO
    {
        $locationData = $this->buildLocationData();
        if (!$this->haveAvailableDeliveries()) {
            return DeliveryiesDTO::make(array_merge([
                'deliveries' => [],
                'selectedId' => 0,
                'storeSelectedId' => 0,
                'minDeliveryPrice' => 0.0,
                'minDeliveryPriceFormatted' => $this->priceService->format(0.0),
                'message' => 'Нет доступных способов доставки',
            ], $locationData));
        }

        $deliveries = $this->deliveries
            ->map(fn(BaseDelivery $delivery) => $this->buildDeliveryDTO($delivery))
            ->toArray();

        return DeliveryiesDTO::make(array_merge([
            'deliveries' => $deliveries,
            'selectedId' => $this->shipment?->getDeliveryId() ?? 0,
            'storeSelectedId' => $this->storeSelectedId,
            'minDeliveryPrice' => $this->getMinPrice(),
            'minDeliveryPriceFormatted' => $this->priceService->format($this->getMinPrice()),
            'message' => '',
        ], $locationData));
    }

    private function buildLocationData(): array
    {
        $props = $this->orderService->getPropertyValues($this->orderProperties);

        return [
            'location' => $props['LOCATION'] ?? '',
            'city' => $props['CITY'] ?? '',
            'address' => $props['ADDRESS'] ?: ($props['CITY'] ?? ''),
            'postCode' => $props['ZIP'] ?? '',
            'selectedPvz' => $props['PICKUP_POINT_ID'] ?? '',
        ];
    }

    private function buildDeliveryDTO(BaseDelivery $delivery): DeliveryDTO
    {
        $extraServices = $delivery->isSelected() ? $this->getExtraServices($delivery->getExtraServices()) : [];
        $storeList = $delivery->isStoreDelivery() && $delivery->isSelected() 
            ? $this->getStoreData($delivery) 
            : [];

        $price  = $delivery->getPrice();
        $period = (string)$delivery->getDeliveryPeriod();

        if (!$delivery->isSelected() && $this->shipment !== null) {
            $handler = $this->deliveryHandlers[$delivery->getId()] ?? null;
            if ($handler && ($price <= 0 || $period === '')) {
                try {
                    $calcResult = $handler->calculate($this->shipment);
                    if ($calcResult->isSuccess()) {
                        if ($price <= 0) {
                            $price = (float)$calcResult->getPrice();
                        }
                        if ($period === '') {
                            $period = $this->formatPeriod(
                                (int)$calcResult->getPeriodFrom(),
                                (int)$calcResult->getPeriodTo(),
                                (string)$calcResult->getPeriodType()
                            );
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        return DeliveryDTO::make([
            'id' => $delivery->getId(),
            'code' => $delivery->getCode(),
            'name' => $delivery->getName(),
            'ownName' => $delivery->getOwnName(),
            'description' => $delivery->getDescription(),
            'currency' => $this->order->getCurrency(),
            'sort' => $delivery->getSort(),
            'logotip' => $delivery->getLogotip(),
            'price' => $price,
            'priceFormatted' => $this->priceService->format($price),
            'deliveryPeriod' => $period,
            'isSelected' => $delivery->isSelected(),
            'extraServices' => $extraServices,
            'isStoreDelivery' => $delivery->isStoreDelivery(),
            'isCourier' => $delivery->isCourier(),
            'isPickup' => $delivery->isPickup(),
            'storeList' => $storeList,
        ]);
    }

    /**
     * Форматирует период доставки из числовых значений.
     */
    private function formatPeriod(int $from, int $to, string $type): string
    {
        if ($from <= 0 && $to <= 0) {
            return '';
        }
        $label = match ($type) {
            'H' => 'ч',
            'M' => 'мин',
            default => 'дн',
        };
        return $from > 0 && $from !== $to ? "{$from}–{$to} {$label}" : "{$to} {$label}";
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

                $extraServicesFormatted[] = ExtraServiceDTO::make([
                    'id' => $extraServiceId,
                    'code' => $extraService->getCode(),
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

        $storeData = service(StoreRepositoryContract::class)->getPickPoints($delivery->getStoreList());
        
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
