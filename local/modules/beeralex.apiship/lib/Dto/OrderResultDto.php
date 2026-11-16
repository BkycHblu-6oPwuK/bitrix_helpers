<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;

/**
 * Результат создания/получения заказа ApiShip.
 */
final class OrderResultDto
{
    use Arrayable;

    public function __construct(
        /** ID заказа в ApiShip */
        public readonly int $orderId,
        /** Дата и время создания заказа */
        public readonly ?string $created,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            orderId: (int)($data['orderId'] ?? 0),
            created: isset($data['created']) ? (string)$data['created'] : null,
        );
    }
}
