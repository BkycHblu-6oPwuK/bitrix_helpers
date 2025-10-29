<?php

namespace App\Catalog\Repository;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\OrderTable;
use App\Catalog\Enum\OrderStatuses;

Loader::includeModule('sale');

class OrderRepository
{

    /**
     * @var OrderTable|string $entity
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = OrderTable::class;
    }
    /**
     * По умолчанию без заказов примерочной
     */
    public function getOrdersIdsByUser(int $userId, bool $filterDressing = true, ?callable $queryFilter = null): array
    {
        $query = $this->entity::query()
            ->setSelect(['ID'])
            ->where('USER_ID', $userId)
            ->setOrder(['ID' => 'DESC']);

        if ($filterDressing) {
            $query->whereNotIn('STATUS_ID', [OrderStatuses::DRESSING->value]);
        }

        if ($queryFilter) {
            $queryFilter($query);
        }

        return $this->fetchOrderIds($query);
    }

    public function getDressingOrdersIdsByUser(int $userId): array
    {
        $query = $this->entity::query()
            ->setSelect(['ID'])
            ->where('USER_ID', $userId)
            ->whereIn('STATUS_ID', [OrderStatuses::DRESSING->value])
            ->setOrder(['ID' => 'DESC']);

        return $this->fetchOrderIds($query);
    }

    private function fetchOrderIds(\Bitrix\Main\ORM\Query\Query $query): array
    {
        return array_column($query->exec()->fetchAll(), 'ID');
    }

    /**
     * @return \Bitrix\Sale\Order[]
     */
    public function getOrdersByIds(array $ordersIds): array
    {
        $orders = [];
        foreach ($ordersIds as $id) {
            $order = \Bitrix\Sale\Order::load($id);
            if ($order) {
                $orders[] = $order;
            }
        }
        return $orders;
    }
}
