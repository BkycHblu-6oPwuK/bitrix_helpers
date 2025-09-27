<?php

namespace Itb\Catalog\Location\Contracts;

interface LocationApiClientInterface
{
    /** 
     * Подсказки по адресу (строка запроса). 
     * 
     * @param string $query 
     * @param int $count 
     * @return array 
     **/ 
    public function suggestAddress(string $query, int $count = 5): array;
    /** 
     * Геолокация по координатам. 
     * 
     * @param float $lat 
     * @param float $lon 
     * @param int $radius 
     * @param int $count 
     * @return array 
     **/ 
    public function geolocate(float $lat, float $lon, int $radius = 100, int $count = 3): array;
}
