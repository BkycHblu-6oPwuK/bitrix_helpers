<?php

namespace Itb\Catalog;

class SizeComparator
{
    protected $sortMap = [
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
    protected $flippedSortMap;

    public function __construct()
    {
        $this->flippedSortMap = array_flip($this->sortMap);
    }

    public function compare(string $a, string $b): int
    {
        $a = mb_strtoupper($a);
        $b = mb_strtoupper($b);

        if (isset($this->flippedSortMap[$a]) && isset($this->flippedSortMap[$b])) {
            return $this->flippedSortMap[$a] - $this->flippedSortMap[$b];
        } elseif (isset($this->flippedSortMap[$a])) {
            return 1;
        } elseif (isset($this->flippedSortMap[$b])) {
            return -1;
        } else {
            return strnatcasecmp($a, $b);
        }
    }
}
