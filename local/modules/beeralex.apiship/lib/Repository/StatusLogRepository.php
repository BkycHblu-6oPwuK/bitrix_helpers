<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Dto\StatusDto;
use Beeralex\Apiship\Model\StatusLogTable;

/**
 * Репозиторий журнала статусов (таблица beeralex_apiship_status_log).
 */
final class StatusLogRepository
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {}

    /**
     * Пишет статус в лог, если он отличается от последнего сохранённого для заказа.
     * Возвращает true, если статус новый (была запись в лог).
     */
    public function logIfChanged(int $bitrixOrderId, StatusDto $status): bool
    {
        $order = $this->orderRepository->find($bitrixOrderId);
        $lastStatusKey = $order['LAST_STATUS_KEY'] ?? null;

        if ($lastStatusKey === $status->getKey()) {
            return false;
        }

        StatusLogTable::add([
            'BITRIX_ORDER_ID'   => $bitrixOrderId,
            'APISHIP_ORDER_ID'  => $status->getOrderId() ?: null,
            'STATUS_KEY'        => $status->getKey(),
            'STATUS_NAME'       => $status->getName(),
            'STATUS_DATE'       => $status->getDate() ? new \Bitrix\Main\Type\DateTime($status->getDate()) : null,
        ]);

        $this->orderRepository->updateLastStatus($bitrixOrderId, $status->getKey());

        return true;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getHistory(int $bitrixOrderId): array
    {
        return StatusLogTable::getList([
            'select' => ['STATUS_KEY', 'STATUS_NAME', 'STATUS_DATE', 'CREATED_AT'],
            'filter' => ['=BITRIX_ORDER_ID' => $bitrixOrderId],
            'order'  => ['CREATED_AT' => 'ASC'],
        ])->fetchAll();
    }
}
