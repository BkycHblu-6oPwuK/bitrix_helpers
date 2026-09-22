<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface PointServiceContract
{
    /**
     * Список ПВЗ с фильтром.
     *
     * @param array<string,mixed> $filter
     * @return \Beeralex\Apiship\Dto\PointDto[]
     */
    public function getPoints(array $filter = [], int $limit = 50, int $offset = 0): array;

    /**
     * Получить конкретный ПВЗ по id.
     */
    public function getPoint(string $id): ?\Beeralex\Apiship\Dto\PointDto;
}
