<?php
namespace Beeralex\Catalog\Helper;

class SizeComparatorHelper
{
    protected static $sortMap = [
        'XS',
        'S',
        'M',
        'L',
        'XL',
        'XXL',
        '2XL',
        'XXXL',
        '3XL',
    ];
    protected static $flippedSortMap = null;

    public static function compare(string $a, string $b): int
    {
        if(static::$flippedSortMap === null) {
            static::$flippedSortMap = array_flip(static::$sortMap);
        }
        $a = mb_strtoupper($a);
        $b = mb_strtoupper($b);

        if (isset(static::$flippedSortMap[$a]) && isset(static::$flippedSortMap[$b])) {
            return static::$flippedSortMap[$a] - static::$flippedSortMap[$b];
        } elseif (isset(static::$flippedSortMap[$a])) {
            return 1;
        } elseif (isset(static::$flippedSortMap[$b])) {
            return -1;
        } else {
            return strnatcasecmp($a, $b);
        }
    }
}
