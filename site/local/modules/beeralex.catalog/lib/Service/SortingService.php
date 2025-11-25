<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Repository\SortingRepository;
use Bitrix\Main\Context;

class SortingService
{
    public function __construct(
        public readonly SortingRepository $sortingRepository
    ) {}

    /**
     * @return array список всех доступных сортировок
     */
    public function getAvailableSortings(): array
    {
        $rows = $this->sortingRepository->all(['ACTIVE' => 'Y']);
        $result = [];
        foreach ($rows as $row) {
            $code = (string)($row['CODE'] ?? $row['ID'] ?? '');
            if ($code === '') {
                continue;
            }

            $sortBy = $row['SORT_BY']['VALUE'] ?? $row['SORT_BY.VALUE'] ?? ($row['SORT'] ?? 'ID');
            $order = strtoupper((string)($row['DIRECTION']['VALUE'] ?? $row['DIRECTION.VALUE'] ?? 'ASC'));

            $result[$code] = [
                'fieldId' => $code,
                'id' => $code,
                'name' => $row['NAME'] ?? $code,
                'sortBy' => $sortBy,
                'order' => $order,
            ];
        }

        if (!empty($result)) {
            return $result;
        }
        return [];
    }

    /**
     * @return string ID сортировки из запроса. Если сортировка не была передана вернет дефолтную сортировку
     */
    public function getRequestedSortIdOrDefault(): string
    {
        $availableSortings = $this->getAvailableSortings();

        $request = Context::getCurrent()->getRequest();
        $requestedSorting = $request->get('sort');
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
            'sortField1' => $sort['sortBy'],
            'sortOrder1' => $sort['order'],
            'sortField2' => 'SORT',
            'sortOrder2' => 'ASC',
        ];
    }

    public function getDefaultSortId(): string
    {
        return $this->sortingRepository->getDefaultSorting(['CODE'])['CODE'] ?? '';
    }
}
