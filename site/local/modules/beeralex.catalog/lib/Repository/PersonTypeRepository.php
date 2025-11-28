<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Bitrix\Main\Loader;
use Beeralex\Core\Repository\Repository;
use Bitrix\Sale\PersonTypeTable;

class PersonTypeRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(PersonTypeTable::class);
    }

    public function getIndividualPersonId(string $siteId, string $name = 'Физическое лицо'): int
    {
        return $this->query()->setSelect(['ID'])->where('LID', $siteId)->where('NAME', $name)->setCacheTtl(86400)->fetch()['ID'];
    }

    public function getLegalPersonId(string $siteId, string $name = 'Юридическое лицо'): int
    {
        return $this->query()->setSelect(['ID'])->where('LID', $siteId)->where('NAME', $name)->setCacheTtl(86400)->fetch()['ID'];
    }
}
