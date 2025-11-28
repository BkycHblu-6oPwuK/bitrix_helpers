<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Loader;

class PriceTypeRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(GroupTable::class);
    }

    public function getBasePriceId(): int
    {
        $basePrice = GroupTable::getBasePriceType();
        return (int)($basePrice['ID'] ?? 0);
    }

    public function getAllIds(): array
    {
        $result = $this->query()
            ->setSelect(['ID'])
            ->setCacheTtl(86400)
            ->fetchAll();

        return array_column($result, 'ID');
    }

    public function getAll(): array
    {
        return $this->query()
            ->setSelect(['*'])
            ->setCacheTtl(86400)
            ->fetchAll();
    }
}
