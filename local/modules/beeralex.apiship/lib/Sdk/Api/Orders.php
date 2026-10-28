<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;
use Beeralex\Apiship\Support\QueryFilterBuilder;

/**
 * Расширенные методы заказов, отсутствующие в штатном SDK.
 */
class Orders extends AbstractExtendedApi
{
    /**
     * История изменения статусов по интервалу.
     * filter: query-filter (providerKey=cdek)
     */
    public function getStatusHistoryByInterval(string $from, string $to, array $filter = [], int $limit = 100, int $offset = 0): RawResponse
    {
        $query = ['from' => $from, 'to' => $to, 'limit' => $limit, 'offset' => $offset];
        $filterStr = QueryFilterBuilder::build($filter);
        if ($filterStr !== '') {
            $query['filter'] = $filterStr;
        }
        return $this->requestGet('orders/statuses/interval', $query);
    }

    /** Ярлыки (этикетки) для заказов. Ответ содержит url и failedOrders. */
    public function getLabels(array $orderIds, string $format = 'pdf', array $extra = []): RawResponse
    {
        $body = array_merge(['orderIds' => array_values($orderIds), 'format' => $format], $extra);
        return $this->requestPost('orders/labels', $body);
    }

    /** Накладные для заказов. */
    public function getWaybills(array $orderIds, array $extra = []): RawResponse
    {
        $body = array_merge(['orderIds' => array_values($orderIds)], $extra);
        return $this->requestPost('orders/waybills', $body);
    }

    /** Валидация заказа до создания. */
    public function validate(array $orderData): RawResponse
    {
        return $this->requestPost('orders/validate', $orderData);
    }

    /** Создать возвратный заказ. */
    public function createReturn(array $orderData): RawResponse
    {
        return $this->requestPost('orders/return', $orderData);
    }

    /** Массовая загрузка заказов (файл). */
    public function upload(array $body): RawResponse
    {
        return $this->requestPost('orders/upload', $body);
    }

    /** Получить состав (items) заказа. */
    public function getItems(int $orderId): RawResponse
    {
        return $this->requestGet('orders/' . $orderId . '/items');
    }

    /** Данные о вызове курьера для заказа. */
    public function getOrderCourier(int $orderId): RawResponse
    {
        return $this->requestGet('orders/' . $orderId . '/courier');
    }

    /** Получить код для заказа. */
    public function getCode(int $orderId): RawResponse
    {
        return $this->requestGet('orders/' . $orderId . '/code');
    }

    /** Получить код получателя. */
    public function getRecipientCode(int $orderId): RawResponse
    {
        return $this->requestGet('orders/' . $orderId . '/recipientcode');
    }

    /** Продлить хранение заказа. */
    public function extendStorageDays(int $orderId, int $days): RawResponse
    {
        return $this->requestPost('orders/' . $orderId . '/extendStorageDays', ['days' => $days]);
    }
}
