<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support\Concerns;

/**
 * toArray() для DTO на readonly-свойствах: вложенные DTO и массивы DTO разворачиваются рекурсивно.
 */
trait Arrayable
{
    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return static::arrayify(get_object_vars($this));
    }

    private static function arrayify(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(static fn($item) => static::arrayify($item), $value);
        }

        return $value;
    }
}
