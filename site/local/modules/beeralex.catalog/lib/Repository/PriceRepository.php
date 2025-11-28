<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Catalog\PriceTable;

class PriceRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(PriceTable::class);
    }
}
