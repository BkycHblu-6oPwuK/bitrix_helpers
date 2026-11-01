<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto\Concerns;

/**
 * Приведение необязательных значений DTO: null и пустая строка -> null, иначе нужный тип.
 */
trait NormalizesValues
{
    protected static function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }

    protected static function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float)$value;
    }

    protected static function nullableString(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string)$value;
    }

    /**
     * @return int[]
     */
    protected static function intList(mixed $value): array
    {
        $list = array_filter(array_map('intval', (array)$value), static fn(int $v) => $v > 0);
        return array_values(array_unique($list));
    }

    /**
     * @return string[]
     */
    protected static function stringList(mixed $value): array
    {
        $list = array_filter(
            array_map(static fn($v) => trim((string)$v), (array)$value),
            static fn(string $v) => $v !== ''
        );
        return array_values(array_unique($list));
    }
}
