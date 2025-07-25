<?php

namespace Itb\Checkout;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\OrderStatus;
use Bitrix\Sale\Property;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;
use Illuminate\Support\Collection;
use Itb\Catalog\PersonType;
use Itb\Enum\OrderStatuses;

class Order
{
    public static function getPropertyValues(PropertyValueCollectionBase $collection): array
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
    public static function getProperties(PropertyValueCollectionBase $collection): array
    {
        $props = [];

        foreach ($collection as $item) {
            $props[$item->getField('CODE')] = $item;
        }

        return $props;
    }

    /**
     * Список всех возможных стран
     *
     * @return array [countryIsoCode => countryName]
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getCountryList(): array
    {
        static $countryList = null;

        if ($countryList === null) {
            $countryPropId = Property::getList([
                'select' => ['ID'],
                'filter' => ['CODE' => 'COUNTRY']
            ])->fetch()['ID'];

            if ($countryPropId) {
                $countryList = Property::getObjectById($countryPropId)->getOptions() ?? [];
            } else {
                $countryList =  [];
            }
        }

        return $countryList;
    }

    /**
     * @return Collection code => prop
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getPropertyList(?int $persontTypeId = null): Collection
    {
        static $allProps = null;

        if ($allProps === null) {
            $allProps = collect(\Bitrix\Sale\Property::getList([
                'filter' => [
                    'PERSON_TYPE_ID' => $persontTypeId ?: PersonType::getIndividualPersonId()
                ]
            ])->fetchAll())
                ->mapWithKeys(function ($prop) {
                    return [$prop['CODE'] => $prop];
                });
        }

        return $allProps;
    }

    /**
     * @param \Bitrix\Sale\Order $order
     * @return BasketItem
     * @throws \Exception
     */
    public static function getBasketItemWithMaxPrice(\Bitrix\Sale\Order $order): BasketItem
    {
        $basketItems = $order->getBasket()->getBasketItems();

        if (empty($basketItems)) {
            throw new \Exception();
        }

        return collect($basketItems)
            ->reduce(function (BasketItem $res, BasketItem $item) {
                return $item->getPrice() > $res->getPrice() ? $item : $res;
            }, $basketItems[0]);
    }

    public static function getQuantity(\Bitrix\Sale\Order $order): int
    {
        return collect($order->getBasket()->getBasketItems())
            ->map(function (BasketItem $basketItem) {
                return $basketItem->getQuantity();
            })
            ->sum();
    }

    public static function getPayerName(\Bitrix\Sale\Order $order): string
    {
        $propertyValues = self::getPropertyValues($order->getPropertyCollection());
        return $propertyValues['NAME'] ?? '';
    }

    public static function getPayerLastName(\Bitrix\Sale\Order $order): string
    {
        $propertyValues = self::getPropertyValues($order->getPropertyCollection());
        return $propertyValues['LAST_NAME'] ?? '';
    }

    public static function getPropId($code): string
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

    public static function getPayerPhone(\Bitrix\Sale\Order $order): string
    {
        $payerPhoneProp = $order->getPropertyCollection()->getPhone();
        if ($payerPhoneProp) {
            return $payerPhoneProp->getValue() ?: '';
        }
        return '';
    }

    public static function getDelivery(\Bitrix\Sale\Order $order): string
    {
        return self::getDeliveryName($order) . '. ' . self::getDeliveryAddress($order);
    }

    public static function getDeliveryName(\Bitrix\Sale\Order $order): string
    {
        /** @var Shipment $shipment */
        $shipment = $order->getShipmentCollection()[0];

        return $shipment->getDeliveryName();
    }

    public static function getDeliveryAddress(\Bitrix\Sale\Order $order): string
    {
        $propertyValues = self::getPropertyValues($order->getPropertyCollection());

        return $propertyValues['ADDRESS'] ?? '';
    }

    public static function getTrackNumber(\Bitrix\Sale\Order $order): string
    {
        $propertyValues = self::getPropertyValues($order->getPropertyCollection());
        return $propertyValues['TRACK_NUMBER'] ?? '';
    }

    public static function getDateFormatted(\Bitrix\Sale\Order $order): string
    {
        /** @var DateTime $date */
        $date = $order->getDateInsert();
        if ($order->getField('STATUS_ID') === OrderStatuses::TRANSIT->value) {
            $now = new \Bitrix\Main\Type\DateTime();
            $interval = $now->getDiff($date);
            if ($interval->days == 0) {
                if ($interval->h == 0) {
                    return '~ ' . FormatDate('idiff', $date->getTimestamp());
                }
                return '~ ' . FormatDate('Hdiff', $date->getTimestamp());
            }
            return '~ ' . FormatDate('Q', $date->getTimestamp());
        }
        return FormatDate('d F', $date->getTimestamp());
    }

    public static function getStatusName(\Bitrix\Sale\Order $order): string
    {
        if ($order->isCanceled()) {
            return 'Отменён';
        }
        return OrderStatus::getAllStatusesNames()[$order->getField('STATUS_ID')] ?? '';
    }

    public static function addShipment(\Bitrix\Sale\Order $order, ?int $deliveryId = null)
    {
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $service = \Bitrix\Sale\Delivery\Services\Manager::getById($deliveryId ?? \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
        $shipment->setFields([
            'DELIVERY_ID' => $service['ID'],
            'DELIVERY_NAME' => $service['NAME'],
        ]);
    }

    public static function addPayment(\Bitrix\Sale\Order $order, int $payId)
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

    public static function setProperty(PropertyValueCollectionBase $propertyCollection, $code, $value)
    {
        if ($property = $propertyCollection->getItemByOrderPropertyCode($code)) {
            $property->setValue($value);
        }
    }

    public static function copyOrder(int $orderId)
    {
        $order = \Bitrix\Sale\Order::load($orderId);
        if ($order) {
            $curBasket = \Itb\Catalog\BasketFacade::getForCurrentUser();
            $curBasket->removeAll();
            foreach ($order->getBasket()->getBasketItems() as $basketItem) {
                $curBasket->add($basketItem->getProductId(), $basketItem->getQuantity());
            }
            $curBasket->save();
        }
    }
}
