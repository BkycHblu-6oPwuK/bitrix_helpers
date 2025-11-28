<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Sale\FuserTable;

class FuserRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(FuserTable::class);
    }

    public function getByUserId(int $userId, string $siteId, array $select = ['*']): ?array
    {
        return $this->query()
            ->setSelect($select)
            ->where('USER_ID', $userId)
            ->where('LID', $siteId)
            ->setCacheTtl(86400)
            ->fetch() ?: null;
    }
}
