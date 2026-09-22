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
    public function getProviders(array $filter = []): array;

    /**
     * Актуальные тарифы (для заполнения CONFIG профиля доставки).
     *
     * @return array<int,array<string,mixed>>
     */
    public function getTariffs(array $filter = []): array;
}
