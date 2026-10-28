<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface ConnectionServiceContract
{
    /**
     * @param array<string,mixed> $filter JSON-фильтр (см. openapi: /connections)
     * @return array<int,array<string,mixed>>
     */
    public function getConnections(array $filter = [], int $limit = 100, int $offset = 0): array;
}
