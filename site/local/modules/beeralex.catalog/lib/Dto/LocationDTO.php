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
     * @param array{LATITUDE: float, LONGITUDE: float} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'latitude' => (float)$data['LATITUDE'],
            'longitude' => (float)$data['LONGITUDE'],
        ]);
    }
}
