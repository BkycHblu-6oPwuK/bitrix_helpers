<?php

namespace Beeralex\Catalog\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property float $latitude
 * @property float $longitude
 */
class LocationDTO extends Resource
{
    /**
     * @param array{LATITUDE?: float, LONGITUDE?: float, GPS_N?: float, GPS_S?: float} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'latitude' => (float)($data['LATITUDE'] ?? $data['GPS_N']),
            'longitude' => (float)($data['LONGITUDE'] ?? $data['GPS_S']),
        ]);
    }
}
