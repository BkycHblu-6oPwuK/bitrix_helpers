<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Model\OrderTable;

/**
 * Репозиторий связи заказ Bitrix <-> заказ ApiShip (таблица beeralex_apiship_order).
 */
final class OrderRepository
{
    /**
     * Создать (или обновить) связь. Один заказ Bitrix — одна активная запись ApiShip.
     */
    public function save(int $bitrixOrderId, int $apiShipOrderId, string $providerKey = '', ?int $tariffId = null): void
    {
        $existing = $this->find($bitrixOrderId);

        if ($existing !== null) {
            OrderTable::update($existing['ID'], [
                'APISHIP_ORDER_ID' => $apiShipOrderId,
                'PROVIDER_KEY'     => $providerKey,
                'TARIFF_ID'        => $tariffId,
                'UPDATED_AT'       => new \Bitrix\Main\Type\DateTime(),
            ]);
            return;
        }

        OrderTable::add([
            'BITRIX_ORDER_ID'   => $bitrixOrderId,
            'APISHIP_ORDER_ID'  => $apiShipOrderId,
            'PROVIDER_KEY'      => $providerKey,
            'TARIFF_ID'         => $tariffId,
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $bitrixOrderId): ?array
    {
        return OrderTable::getList([
            'select' => ['ID', 'BITRIX_ORDER_ID', 'APISHIP_ORDER_ID', 'PROVIDER_KEY', 'TARIFF_ID', 'LAST_STATUS_KEY'],
            'filter' => ['=BITRIX_ORDER_ID' => $bitrixOrderId],
            'limit'  => 1,
        ])->fetch() ?: null;
    }

    public function getApiShipOrderId(int $bitrixOrderId): ?int
    {
        $row = $this->find($bitrixOrderId);
        return $row !== null ? (int)$row['APISHIP_ORDER_ID'] : null;
    }

    /**
     * Обратный поиск: по ID заказа ApiShip найти заказ Bitrix (нужен для обработки вебхуков).
     */
    public function getBitrixOrderId(int $apiShipOrderId): ?int
    {
        $row = OrderTable::getList([
            'select' => ['BITRIX_ORDER_ID'],
            'filter' => ['=APISHIP_ORDER_ID' => $apiShipOrderId],
            'limit'  => 1,
        ])->fetch();

        return $row !== false ? (int)$row['BITRIX_ORDER_ID'] : null;
    }

    public function updateLastStatus(int $bitrixOrderId, string $statusKey): void
    {
        $existing = $this->find($bitrixOrderId);
        if ($existing === null) {
            return;
        }

        OrderTable::update($existing['ID'], [
            'LAST_STATUS_KEY' => $statusKey,
            'UPDATED_AT'      => new \Bitrix\Main\Type\DateTime(),
        ]);
    }
}
