<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Enum\DeliveryType;
use Beeralex\Apiship\Support\Concerns\Arrayable;

/**
 * Нормализованный тариф расчёта доставки.
 */
class TariffDto
{
    use Arrayable;

    public readonly bool $isToPoint;

    public function __construct(
        public readonly string $providerKey,
        public readonly int $tariffId,
        public readonly string $tariffName,
        public readonly float $deliveryCost,
        public readonly int $daysMin,
        public readonly int $daysMax,
        /** 1 — до двери, 2 — до ПВЗ */
        public readonly int $deliveryType,
        public readonly array $pointIds,
    ) {
        $this->isToPoint = $deliveryType === DeliveryType::TO_POINT->value;
    }

    public static function make(array $data): static
    {
        $deliveryType = (int)($data['deliveryType'] ?? 0);

        return new static(
            providerKey: (string)($data['providerKey'] ?? ''),
            tariffId: (int)($data['tariffId'] ?? 0),
            tariffName: (string)($data['tariffName'] ?? ''),
            deliveryCost: (float)($data['deliveryCost'] ?? 0),
            daysMin: (int)($data['daysMin'] ?? 0),
            daysMax: (int)($data['daysMax'] ?? 0),
            deliveryType: $deliveryType,
            pointIds: (array)($data['pointIds'] ?? []),
        );
    }
}
