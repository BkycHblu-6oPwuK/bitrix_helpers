<?php

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Itb\Catalog\Order as CatalogOrder;
use Itb\Catalog\Enum\OrderStatuses;
use Itb\User\User;

class ItbOrderController extends Controller
{
    public function configureActions()
    {
        return [
            'cancel' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Authentication()
                ],
                'postfilters' => [],
            ],
            'copyOrder' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Authentication()
                ],
            ],
            'changeStore' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Authentication()
                ],
            ],
        ];
    }

    public function cancelAction($id)
    {
        try {
            Loader::includeModule('sale');

            $user = User::current();

            $order = Order::load($id);
            if (!$order || (int)$order->getUserId() !== $user->getId()) {
                return [
                    'success' => false,
                    'error' => 'Невозможно отменить заказ.'
                ];
            }
            $order->setField('CANCELED', 'Y');
            $order->setField('REASON_CANCELED', 'Отменён пользователем');
            $order->setField('STATUS_ID', OrderStatuses::CANCELED->value);
            $order->save();
            return [
                'success' => true,
                'data' => [
                    'status' => CatalogOrder::getStatusName($order),
                    'date' => CatalogOrder::getDateFormatted($order)
                ] // или сделать builder для заказа чтобы возвращать весь заказ
            ];
        } catch (\Exception $e) {
        }
        return [
            'success' => false
        ];
    }

    public function copyOrderAction($orderId)
    {
        try {
            if (!Loader::includeModule('sale')) {
                throw new \Exception('Модуль sale не подключен');
            }
            CatalogOrder::copyOrder($orderId);
            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public function changeStoreAction($orderId, $storeId)
    {
        try {
            if (!Loader::includeModule('sale')) {
                throw new \Exception('Модуль sale не подключен');
            }

            $order = Bitrix\Sale\Order::load($orderId);
            if (!$order) {
                throw new \Exception('Заказ не найден');
            }

            $shipmentCollection = $order->getShipmentCollection();

            foreach ($shipmentCollection as $shipment) {
                if ($shipment->isSystem()) {
                    continue;
                }

                $shipmentItemCollection = $shipment->getShipmentItemCollection();

                foreach ($shipmentItemCollection as $shipmentItem) {
                    $basketItem = $shipmentItem->getBasketItem();
                    $shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();

                    foreach ($shipmentItemStoreCollection as $storeItem) {
                        $storeItem->delete();
                    }

                    $shipmentItemStore = $shipmentItemStoreCollection->createItem($basketItem);
                    $shipmentItemStore->setFields([
                        'STORE_ID' => $storeId,
                        'QUANTITY' => $shipmentItem->getQuantity(),
                    ]);
                }

                $shipment->setStoreId($storeId);
            }

            $order->save();

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function initPayAction($orderId, $sendSms = 0)
    {
        try {
            $order  = \Bitrix\Sale\Order::load($orderId);
            $filter = $sendSms ? fn(\Bitrix\Sale\Payment $payment) => str_ends_with(strtolower($payment->getPaySystem()->getField('CODE') ?: ''), 'sms') : null;
            $result = CatalogOrder::initPay($order, $filter);
            if (!$result->isResultApplied()) {
                throw new \Exception();
            }
            return [
                'url' => $sendSms ? '' : $result->getPaymentUrl(),
                'success' => true,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
            ];
        }
    }
}
