<?php
namespace Itb\Catalog;

use Bitrix\Main\Context;

class Sorting
{
    /**
     * @return array список всех доступных сортировок
     */
    public static function getAvailableSortings(): array
    {
        return [
            'new' => [
                'fieldId' => 'new',
                'id'      => 'new',
                'name'    => 'По новизне',
                'sortBy'  => 'ID',
                'order'   => 'DESC',
            ],
            'price_asc' => [
                'fieldId' => 'price_asc',
                'id'      => 'price_asc',
                'name'    => 'Сначала дешевле',
                'sortBy'  => 'PROPERTY_PRICE_BY_SORT',
                'order'   => 'ASC,nulls',
            ],
            'price_desc' => [
                'fieldId' => 'price_desc',
                'id'      => 'price_desc',
                'name'    => 'Сначала дороже',
                'sortBy'  => 'PROPERTY_PRICE_BY_SORT',
                'order'   => 'DESC,nulls',
            ],
        ];
    }

    /**
     * @return string ID сортировки из запроса. Если сортировка не была передана вернет дефолтную сортировку
     */
    public static function getRequestedSortIdOrDefault(): string
    {
        $availableSortings = static::getAvailableSortings();

        $request = Context::getCurrent()->getRequest();
        $requestedSorting = $request->get('sort');
        if (is_string($requestedSorting) && $availableSortings[$requestedSorting]) {
            return $requestedSorting;
        } else {
            return static::getDefaultSortId();
        }
    }

    /**
     * @return array параметры для сортировки для catalog.section
     */
    public static function getRequestedSort(): array
    {
        $sort = static::getAvailableSortings()[static::getRequestedSortIdOrDefault()];
        return [
            'sortField1' => $sort['sortBy'],
            'sortOrder1' => $sort['order'],
            'sortField2' => 'SORT',
            'sortOrder2' => 'ASC',
        ];
    }

    public static function getDefaultSortId(): string
    {
        return 'price_asc';
    }
}
