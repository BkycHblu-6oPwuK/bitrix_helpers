<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

use Beeralex\Apiship\Dto\StatusDto;

interface StatusServiceContract
{
    /** Получить текущий статус заказа ApiShip. */
    public function getStatus(int $apiShipOrderId): StatusDto;

    /**
     * Получить статусы по нескольким заказам.
     *
     * @param int[] $apiShipOrderIds
     * @return StatusDto[]
     */
    public function getStatuses(array $apiShipOrderIds): array;

    /** Записать полученный статус в лог и вернуть, изменился ли статус. */
    public function logStatus(int $bitrixOrderId, StatusDto $status): bool;
}
