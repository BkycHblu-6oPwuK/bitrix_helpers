<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

interface CourierServiceContract
{
    /**
     * Вызов курьера.
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function call(array $data): array;

    /** Отмена вызова курьера. */
    public function cancel(int $courierCallId): bool;
}
