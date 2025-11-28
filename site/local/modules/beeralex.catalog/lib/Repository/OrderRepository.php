<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Sale\Internals\OrderTable;

class OrderRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(OrderTable::class);
    }
    /**
     * По умолчанию без заказов примерочной
     */
    public function getOrdersIdsByUser(int $userId): array
    {
        $query = $this->query()
            ->setSelect(['ID'])
            ->where('USER_ID', $userId)
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
