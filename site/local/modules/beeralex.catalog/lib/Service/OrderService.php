<?php
namespace Beeralex\Catalog\Helper;

use Beeralex\Catalog\Repository\PersonTypeRepository;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderStatus;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;

class OrderService
{
    public function __construct(
        protected readonly PersonTypeRepository $personTypeRepository
    ) {}

    public function getPropertyValues(PropertyValueCollectionBase $collection): array
    {
        $values = [];
        /** @var PropertyValue $item */
        foreach ($collection as $item) {
            $values[$item->getField('CODE')] = $item->getField('VALUE');
        }

        return $values;
    }

    /**
     * @param PropertyValueCollectionBase $collection
     *
     * @return PropertyValue[]
     */
    public function getProperties(PropertyValueCollectionBase $collection): array
    {
        $props = [];

        foreach ($collection as $item) {
            $props[$item->getField('CODE')] = $item;
        }

        return $props;
    }

    /**
     * @return array code => prop
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getPropertyList(?int $persontTypeId = null): array
    {
        $result = [];
        $props = \Bitrix\Sale\Property::getList([
            'filter' => [
                'PERSON_TYPE_ID' => $persontTypeId ?: $this->personTypeRepository->getIndividualPersonId(SITE_ID)
            ],
            'cache' => [
                'ttl' => 3600,
            ],
        ])->fetchAll();
        
        foreach ($props as $prop) {
            $result[$prop['CODE']] = $prop;
        }
        
        return $result;
    }

    /**
     * @param Order $order
     * @return BasketItem
     */
    public function getBasketItemWithMaxPrice(Order $order): ?BasketItem
    {
        $basketItems = $order->getBasket()->getBasketItems();

        if (empty($basketItems)) {
            return null;
        }

        $maxItem = $basketItems[0];
        foreach ($basketItems as $item) {
            if ($item->getPrice() > $maxItem->getPrice()) {
                $maxItem = $item;
            }
        }

        return $maxItem;
    }

    public function getQuantity(Order $order): int
    {
        $totalQuantity = 0;
        foreach ($order->getBasket()->getBasketItems() as $basketItem) {
            $totalQuantity += $basketItem->getQuantity();
        }

        return $totalQuantity;
    }

    public function getPayerName(Order $order): string
    {
        $propertyValues = $this->getPropertyValues($order->getPropertyCollection());
        return $propertyValues['NAME'] ?? '';
    }

    public function getPayerLastName(Order $order): string
    {
        $propertyValues = $this->getPropertyValues($order->getPropertyCollection());
        return $propertyValues['LAST_NAME'] ?? '';
    }

    public function getPropId(string $code): string
    {
        $res = \CSaleOrderProps::GetList(
            [],
            [
                'CODE' => $code
            ],
            false,
            false,
            ['ID']
        );

        if ($prop = $res->Fetch()) {
            return $prop['ID'];
        }
        return 0;
    }

    public function getPayerPhone(Order $order): string
    {
        $payerPhoneProp = $order->getPropertyCollection()->getPhone();
        if ($payerPhoneProp) {
            return $payerPhoneProp->getValue() ?: '';
        }
        return '';
    }

    public function getDelivery(Order $order): string
    {
        return $this->getDeliveryName($order) . '. ' . $this->getDeliveryAddress($order);
    }

    public function getDeliveryName(Order $order): string
    {
        /** @var Shipment $shipment */
        $shipment = $order->getShipmentCollection()[0];

        return $shipment->getDeliveryName();
    }

    public function getDeliveryAddress(Order $order): string
    {
        $propertyValues = $this->getPropertyValues($order->getPropertyCollection());

        return $propertyValues['ADDRESS'] ?? '';
    }

    public function getTrackNumber(Order $order): string
    {
        $propertyValues = $this->getPropertyValues($order->getPropertyCollection());
        return $propertyValues['TRACK_NUMBER'] ?? '';
    }

    public function getDateFormatted(Order $order): string
    {
        /** @var DateTime $date */
        $date = $order->getDateInsert();
        return FormatDate('d F', $date->getTimestamp());
    }

    public function getStatusName(Order $order): string
    {
        if ($order->isCanceled()) {
            return 'Отменён';
        }
        return OrderStatus::getAllStatusesNames()[$order->getField('STATUS_ID')] ?? '';
    }

    public function addShipment(Order $order, ?int $deliveryId = null)
    {
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $service = \Bitrix\Sale\Delivery\Services\Manager::getById($deliveryId ?? \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
        $shipment->setFields([
            'DELIVERY_ID' => $service['ID'],
            'DELIVERY_NAME' => $service['NAME'],
        ]);
    }

    public function addPayment(Order $order, int $payId)
    {
        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem();
        $paySystemService = \Bitrix\Sale\PaySystem\Manager::getObjectById($payId);
        if (!$paySystemService) {
            throw new \Exception('Pay system not found');
        }
        $payment->setFields([
            'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
            'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
        ]);
    }

    public function setProperty(PropertyValueCollectionBase $propertyCollection, $code, $value)
    {
        if ($property = $propertyCollection->getItemByOrderPropertyCode($code)) {
            $property->setValue($value);
        }
    }

    public function copyOrder(int $orderId)
    {
        $order = Order::load($orderId);
        if ($order) {
            $curBasket = \Beeralex\Catalog\Basket\BasketFacade::getForCurrentUser();
            $curBasket->removeAll();
            foreach ($order->getBasket()->getBasketItems() as $basketItem) {
                $curBasket->add($basketItem->getProductId(), $basketItem->getQuantity());
            }
            $curBasket->save();
        }
    }

    public function initPay(Order $order, ?callable $filterPayment = null): \Bitrix\Sale\PaySystem\ServiceResult
    {
        $paymentCollection = $order->getPaymentCollection();
        $payment = null;
        if ($filterPayment) {
            foreach ($paymentCollection as $paymentItem) {
                if ($filterPayment($paymentItem)) {
                    $payment = $paymentItem;
                    break;
                }
            }
        } else {
            $payment = $paymentCollection[0];
        }

        if (!$payment) return (new \Bitrix\Sale\PaySystem\ServiceResult())->addError(new \Bitrix\Main\Error('not found payment'));
        $service  = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
        return $service->initiatePay($payment, \Bitrix\Main\Context::getCurrent()->getRequest(), \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
    }
}
