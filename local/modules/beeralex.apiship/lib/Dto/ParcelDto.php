<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Одно грузовое место.
 * Единицы: вес — граммы, габариты — сантиметры.
 *
 * @property int $weight
 * @property int $width
 * @property int $height
 * @property int $length
 */
final class ParcelDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'weight' => max(0, (int)ceil((float)($data['weight'] ?? 0))),
            'width'  => max(0, (int)ceil((float)($data['width'] ?? 0))),
            'height' => max(0, (int)ceil((float)($data['height'] ?? 0))),
            'length' => max(0, (int)ceil((float)($data['length'] ?? 0))),
        ]);
    }
}
