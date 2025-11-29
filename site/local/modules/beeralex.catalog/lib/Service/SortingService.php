<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Repository\SortingRepository;
use Bitrix\Main\Context;

class SortingService
{
    public const REQUEST_PARAM = 'sort';

    public function __construct(
        public readonly SortingRepository $sortingRepository
    ) {}

    public function getSorting(): array
    {
        $currentSortId = $this->getRequestedSortIdOrDefault();
        $availableSorting = $this->getAvailableSortings();
        $sortTitle = $availableSorting[$currentSortId]['NAME'];
        return [
            'CURRENT_SORT_ID'    => $currentSortId,
            'DEFAULT_SORT_ID'    => $this->getDefaultSortId(),
            'TITLE'            => $sortTitle,
            'AVAILABLE_SORTING' => array_values($availableSorting)
        ];
    }

    /**
     * @return array список всех доступных сортировок
     */
    public function getAvailableSortings(): array
    {
        $sortings = $this->sortingRepository->all(
            filter: ['ACTIVE' => 'Y'],
            cacheTtl: 3600,
            cacheJoins: true
        );

        $result = [];
        foreach ($sortings as $sorting) {
            $result[$sorting['CODE']] = $sorting;
        }

        return $result;
    }

    /**
     * @return string ID сортировки из запроса. Если сортировка не была передана вернет дефолтную сортировку
     */
    public function getRequestedSortIdOrDefault(): string
    {
        $availableSortings = $this->getAvailableSortings();

        $request = Context::getCurrent()->getRequest();
        $requestedSorting = $request->get(static::REQUEST_PARAM);
        if (is_string($requestedSorting) && isset($availableSortings[$requestedSorting])) {
            return $requestedSorting;
        }

        return $this->getDefaultSortId();
    }

    /**
     * @return array параметры для сортировки для catalog.section
     */
    public function getRequestedSort(): array
    {
        $sorts = $this->getAvailableSortings();
        $sort = $sorts[$this->getRequestedSortIdOrDefault()];
        return [
            'SORT_FIELD_1' => $sort['SORT_BY']['VALUE'],
            'SORT_ORDER_1' => $sort['DIRECTION']['VALUE'],
            'SORT_FIELD_2' => 'SORT',
            'SORT_ORDER_2' => 'ASC',
        ];
    }

    public function getDefaultSortId(): string
    {
        return $this->sortingRepository->getDefaultSorting(
            select: ['CODE'],
            cacheTtl: 3600,
            cacheJoins: true
        )['CODE'] ?? '';
    }
}
