<?php

namespace App\Catalog\Location\Contracts;

interface BitrixLocationResolverContract
{
    const OLD_LOCATION_REQUEST_KEY = 'OLD_LOCATION';
    const CACHE_TIME = 3600000;
    /**
     * Получить местоположение в Битрикс по адресу или координатам
     * для хорошего поиска нужно сделать импорт местоположений до сёл
     * @param string|array $location
     * @return null|array ['city' => string, 'code' => int]
     */
    public function getBitrixLocationByAddress(string|array $location): ?array;
}
