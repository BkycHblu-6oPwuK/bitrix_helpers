<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface ProviderServiceContract
{
    /**
     * Список служб доставки (ТК), подключённых в ApiShip.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getProviders(int $limit = 999, int $offset = 0, array $filter = []): array;

    /**
     * Актуальные тарифы (для заполнения CONFIG профиля доставки).
     *
     * @return array<int,array<string,mixed>>
     */
    public function getTariffs(int $limit = 100, int $offset = 0, array $filter = [], array $fields = []): array;
}
