<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\IblockRepository;

class SortingRepository extends IblockRepository
{
    public function __construct(
        string $iblockCode = 'sorting',
    ) {
        parent::__construct($iblockCode);
    }

    public function all(array $filter = [], array $select = ['ID', 'CODE', 'SORT', 'DEFAULT.ITEM.VALUE', 'DIRECTION.VALUE', 'SORT_BY.VALUE'], array $order = ['SORT' => 'ASC', 'ID' => 'ASC'], int $cacheTtl = 0, bool $cacheJoins = false): array
    {
        return parent::all($filter, $select, $order, $cacheTtl, $cacheJoins);
    }

    public function one(array $filter = [], array $select = ['ID', 'CODE', 'SORT', 'DEFAULT.ITEM.VALUE', 'DIRECTION.VALUE', 'SORT_BY.VALUE'], int $cacheTtl = 0, bool $cacheJoins = false): ?array
    {
        return parent::one($filter, $select, $cacheTtl, $cacheJoins);
    }

    public function getDefaultSorting(array $select = ['ID', 'CODE', 'SORT', 'DEFAULT.ITEM.VALUE', 'DIRECTION.VALUE', 'SORT_BY.VALUE'], int $cacheTtl = 0, bool $cacheJoins = false): ?array
    {
        return $this->one(['DEFAULT.ITEM.VALUE' => 'Y'], $select, $cacheTtl, $cacheJoins);
    }
}
