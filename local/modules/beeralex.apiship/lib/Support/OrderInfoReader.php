<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Repository\StatusLogRepository;
use Beeralex\Apiship\Repository\WebhookLogRepository;

/**
 * Собирает для карточки заказа в админке всё, что модуль знает про заказ ApiShip:
 * связь с заказом, историю статусов и журнал вебхуков. Даты приходят уже строками.
 */
class OrderInfoReader
{
    public function __construct(
        protected readonly OrderRepository $orders,
        protected readonly StatusLogRepository $statuses,
        protected readonly WebhookLogRepository $webhooks,
    ) {}

    /**
     * @return array{order: ?array<string,mixed>, statuses: array<int,array<string,mixed>>, webhooks: array<int,array<string,mixed>>}
     */
    public function read(int $bitrixOrderId, int $limit = 30): array
    {
        $order = $this->orders->findByBitrixOrderId($bitrixOrderId);
        if ($order === null) {
            return ['order' => null, 'statuses' => [], 'webhooks' => []];
        }

        $apiShipOrderId = (int)$order['APISHIP_ORDER_ID'];

        return [
            'order'    => $this->formatDates(
                $order,
                ['STATUS_DATE', 'APISHIP_CREATED_AT', 'CREATED_AT', 'UPDATED_AT', 'PICKUP_DATE', 'DELIVERY_DATE', 'PLANNED_DELIVERY_DATE'],
                ['PICKUP_DATE', 'DELIVERY_DATE', 'PLANNED_DELIVERY_DATE']
            ),
            'statuses' => array_map(
                fn(array $row) => $this->formatDates($row, ['CREATED', 'CREATED_PROVIDER', 'CREATED_AT']),
                $this->statuses->getHistory($bitrixOrderId, $limit)
            ),
            'webhooks' => array_map(
                fn(array $row) => $this->formatDates($row, ['CREATED_AT']),
                $this->webhooks->getByApiShipOrderId($apiShipOrderId, $limit)
            ),
        ];
    }

    /**
     * @param array<string,mixed> $row
     * @param string[] $columns
     * @param string[] $dateOnly колонки, где время не нужно
     * @return array<string,mixed>
     */
    protected function formatDates(array $row, array $columns, array $dateOnly = []): array
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = DateHelper::format($row[$column], !in_array($column, $dateOnly, true));
            }
        }

        return $row;
    }
}
