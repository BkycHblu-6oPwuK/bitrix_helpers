<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;

/**
 * Статус заказа ApiShip.
 */
final class StatusDto
{
    use Arrayable;

    public function __construct(
        /** ID заказа в ApiShip */
        public readonly int $orderId,
        /** Код статуса ApiShip */
        public readonly string $key,
        /** Название статуса */
        public readonly ?string $name,
        /** Дата и время изменения статуса */
        public readonly ?string $date,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            orderId: (int)($data['orderId'] ?? 0),
            key: (string)($data['key'] ?? ''),
            name: isset($data['name']) ? (string)$data['name'] : null,
            date: isset($data['date']) ? (string)$data['date'] : null,
        );
    }
}
