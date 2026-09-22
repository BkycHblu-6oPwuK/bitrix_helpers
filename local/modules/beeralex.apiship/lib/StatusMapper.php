<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Bitrix\Sale\Order;
use Beeralex\Apiship\Dto\StatusDto;
use Beeralex\Apiship\Repository\StatusLogRepository;

/**
 * Маппинг статусов ApiShip -> статусы заказа Bitrix + применение статуса.
 *
 * Стандартная карта покрывает типовые ключи статусов ApiShip (см. openapi.yaml /lists/statuses,
 * а также lib/Sdk/Api/Lists::getStatuses()). Карта не претендует на полноту — конкретные ключи
 * ApiShip зависят от подключённых ТК; при необходимости переопределите getMap() или расширьте
 * настройками модуля (см. docs/JOURNAL.md TODO).
 */
final class StatusMapper
{
    /**
     * ApiShip status key => Bitrix STATUS_ID.
     * Ключи заказа Bitrix — стандартные из демо-набора статусов ("N" новый, ... "F" выполнен,
     * "CANCEL" отменён). Скорректируйте под реальный набор статусов проекта.
     */
    private const DEFAULT_MAP = [
        'created'          => 'N',
        'confirmed'        => 'AP',
        'inTransitToClient'=> 'PD',
        'arrivedAtPoint'   => 'PD',
        'delivered'        => 'F',
        'returned'         => 'CANCEL',
        'cancelled'        => 'CANCEL',
        'error'            => 'CANCEL',
    ];

    public function __construct(
        private readonly StatusLogRepository $statusLogRepository
    ) {}

    /**
     * @return array<string,string>
     */
    protected function getMap(): array
    {
        return self::DEFAULT_MAP;
    }

    public function mapToBitrixStatus(string $apiShipStatusKey): ?string
    {
        return $this->getMap()[$apiShipStatusKey] ?? null;
    }

    /**
     * Применяет статус к заказу Bitrix (если статус смаппен и заказ найден) и пишет лог.
     */
    public function applyToBitrixOrder(int $bitrixOrderId, string $apiShipStatusKey): bool
    {
        $this->statusLogRepository->logIfChanged($bitrixOrderId, StatusDto::make([
            'orderId' => 0,
            'key'     => $apiShipStatusKey,
        ]));

        $bitrixStatusId = $this->mapToBitrixStatus($apiShipStatusKey);
        if ($bitrixStatusId === null) {
            return false;
        }

        $order = Order::load($bitrixOrderId);
        if ($order === null) {
            return false;
        }

        if ($order->getField('STATUS_ID') === $bitrixStatusId) {
            return true;
        }

        $order->setField('STATUS_ID', $bitrixStatusId);
        $result = $order->save();

        return $result->isSuccess();
    }
}
