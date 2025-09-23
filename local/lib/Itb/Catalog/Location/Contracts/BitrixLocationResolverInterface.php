<?php
namespace Itb\Catalog\Location\Contracts;

interface BitrixLocationResolverInterface
{
    /**
     * Получить местоположение в Битрикс по адресу или координатам
     * для хорошего поиска нужно сделать импорт местоположений до сёл
     * @param string|array $location
     * @return null|array ['city' => string, 'code' => int]
     */
    public function getBitrixLocationByAddress(string|array $location): ?array;
}
