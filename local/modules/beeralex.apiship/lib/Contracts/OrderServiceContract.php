<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

use Beeralex\Apiship\Dto\CreateOrderDto;
use Beeralex\Apiship\Dto\OrderResultDto;

interface OrderServiceContract
{
    /** Создать заказ в ApiShip (асинхронно, orders). */
    public function create(CreateOrderDto $dto): OrderResultDto;

    /** Отменить заказ. */
    public function cancel(int $apiShipOrderId): bool;

    /** Удалить заказ. */
    public function delete(int $apiShipOrderId): bool;

    /** Получить информацию о заказе. */
    public function getInfo(int $apiShipOrderId): array;

    /**
     * Создать (или получить существующий) заказ ApiShip для заказа Bitrix
     * и сохранить связь в beeralex_apiship_order.
     */
    public function createForBitrixOrder(int $bitrixOrderId): OrderResultDto;
}
