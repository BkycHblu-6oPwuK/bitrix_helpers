<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface PointServiceContract
{
    /**
     * Список ПВЗ. Фильтр — query-filter формата ApiShip (city=Москва;providerKey=cdek).
     *
     * @param array<string,scalar|scalar[]|null> $filter
     * @return array{items: \Beeralex\Apiship\Dto\PointDto[], total: int}
     */
    public function getPoints(array $filter = [], int $limit = 50, int $offset = 0): array;

    public function getPoint(string $id): ?\Beeralex\Apiship\Dto\PointDto;

    /**
     * Записывает ID выбранного ПВЗ в свойство заказа Bitrix (код свойства из Options::propPickupPointId).
     * Возвращает false если заказ не найден или сохранение не прошло.
     */
    public function setPickupPoint(int $bitrixOrderId, string $pointId): bool;
}
