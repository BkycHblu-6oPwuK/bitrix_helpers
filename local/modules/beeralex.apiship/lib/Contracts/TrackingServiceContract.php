<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface TrackingServiceContract
{
    /**
     * Получить трек-историю заказа для отображения покупателю.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getHistory(int $apiShipOrderId): array;
}
