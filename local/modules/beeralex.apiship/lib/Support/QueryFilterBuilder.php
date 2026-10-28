<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Support;

/**
 * Строит строковый query-filter для ApiShip по правилам документации:
 * https://docs.apiship.ru/docs/api/query-filter/
 *
 * Формат: field=value;field2=value2;field3=[val1,val2]
 * Используется в /lists/points, /lists/statuses, /lists/tariffs, /lists/providers и /orders/statuses/interval.
 * НЕ используется для /connections (там JSON-фильтр).
 */
final class QueryFilterBuilder
{
    /**
     * @param array<string,scalar|scalar[]|null> $filter
     */
    public static function build(array $filter): string
    {
        $parts = [];
        foreach ($filter as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            if (is_array($value)) {
                $parts[] = $key . '=[' . implode(',', array_map('strval', $value)) . ']';
            } else {
                $parts[] = $key . '=' . $value;
            }
        }
        return implode(';', $parts);
    }
}
