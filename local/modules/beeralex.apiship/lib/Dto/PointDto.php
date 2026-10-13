<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Пункт выдачи/приёма заказов (ПВЗ).
 *
 * @property string $id
 * @property string $providerKey
 * @property string $name
 * @property string $address
 * @property float|null $lat
 * @property float|null $lng
 */
final class PointDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id'          => (string)($data['id'] ?? ''),
            'providerKey' => (string)($data['providerKey'] ?? ''),
            'name'        => (string)($data['name'] ?? ''),
            'address'     => (string)($data['address'] ?? ''),
            'lat'         => isset($data['lat']) ? (float)$data['lat'] : null,
            'lng'         => isset($data['lng']) ? (float)$data['lng'] : null,
        ]);
    }
}
