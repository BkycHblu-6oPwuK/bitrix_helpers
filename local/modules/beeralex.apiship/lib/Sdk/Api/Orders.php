<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Расширенные методы заказов, отсутствующие в штатном SDK.
 *
 * Штатный Apiship\Api\Orders покрывает create/update/delete/cancel/getOrderInfo/resend
 * и статусы. Здесь: список заказов, история статусов по интервалу, ярлыки (labels).
 */
class Orders extends AbstractExtendedApi
{
    /**
     * Список заказов с фильтром/пагинацией.
     *
     * @param array<string,mixed> $filter
     */
    public function list(int $limit = 20, int $offset = 0, array $filter = []): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        if ($filter) {
            $query['filter'] = json_encode($filter, JSON_UNESCAPED_UNICODE);
        }
        return $this->requestGet('orders', $query);
    }

    /**
     * История изменения всех статусов по интервалу.
     *
     * @param array<string,mixed> $filter
     */
    public function getStatusHistoryByInterval(string $from, string $to, array $filter = [], int $limit = 100, int $offset = 0): RawResponse
    {
        $query = ['from' => $from, 'to' => $to, 'limit' => $limit, 'offset' => $offset];
        if ($filter) {
            $query['filter'] = json_encode($filter, JSON_UNESCAPED_UNICODE);
        }
        return $this->requestGet('orders/statuses/interval', $query);
    }

    /**
     * Ярлыки (этикетки) для заказов.
     *
     * @param array<int,int|string> $orderIds
     */
    public function getLabels(array $orderIds, string $format = 'pdf', array $extra = []): RawResponse
    {
        $body = array_merge(['orderIds' => array_values($orderIds), 'format' => $format], $extra);
        return $this->requestPost('orders/labels', $body);
    }
}
