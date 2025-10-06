<?php

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use App\Catalog\Helper\OrderHelper;
use App\Catalog\Enum\OrderStatuses;
use App\User\User;

class BeeralexOrderController extends Controller
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
            'getSdekPickupPointForCity' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
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
                    'status' => OrderHelper::getStatusName($order),
                    'date' => OrderHelper::getDateFormatted($order)
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
            OrderHelper::copyOrder($orderId);
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

    /**
     * @todo было бы неплохо перенести этот метод в другой компонент, где будет не только сдек
     */
    public function getSdekPickupPointForCity(string $city = '')
    {
        global $APPLICATION, $SDEK_PICKUP_RESULT;

        $city = trim(strip_tags($city));
        if (empty($city)) {
            return [
                'success' => false,
                'error' => 'Город не указан',
            ];
        }

        $_SESSION['IPOLSDEK_city'] = $city;
        $SDEK_PICKUP_RESULT = null;

        $APPLICATION->IncludeComponent(
            "ipol:ipol.sdekPickup",
            "ajax",
            [
                "CNT_BASKET"      => "N",
                "CNT_DELIV"       => "Y",
                "COUNTRIES"       => [],
                "FORBIDDEN"       => [],
                "MODE"            => "both",
                "NOMAPS"          => "N",
                "PAYER"           => "1",
                "PAYSYSTEM"       => "1",
                "SEARCH_ADDRESS"  => "N",
            ]
        );

        if (!empty($SDEK_PICKUP_RESULT)) {
            return $SDEK_PICKUP_RESULT;
        }

        return [
            'success' => false,
        ];
    }
}
