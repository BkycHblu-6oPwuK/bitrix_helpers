<?php

namespace Beeralex\Catalog\Location\Contracts;

use Beeralex\Catalog\Dto\LocationDTO;

interface BitrixLocationResolverContract
{
    /**
     * Получить местоположение в Битрикс по адресу или координатам
     * для хорошего поиска нужно сделать импорт местоположений до сёл
     * @return null|array ['city' => string, 'code' => int]
     */
    public function getBitrixLocationByAddress(string|LocationDTO $location): ?array;
}
