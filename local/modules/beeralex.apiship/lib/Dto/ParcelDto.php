<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;

/**
 * Одно грузовое место.
 * Единицы: вес — граммы, габариты — сантиметры.
 */
class ParcelDto
{
    use Arrayable;

    public function __construct(
        public readonly int $weight,
        public readonly int $width,
        public readonly int $height,
        public readonly int $length,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            weight: max(0, (int)ceil((float)($data['weight'] ?? 0))),
            width: max(0, (int)ceil((float)($data['width'] ?? 0))),
            height: max(0, (int)ceil((float)($data['height'] ?? 0))),
            length: max(0, (int)ceil((float)($data['length'] ?? 0))),
        );
    }
}
