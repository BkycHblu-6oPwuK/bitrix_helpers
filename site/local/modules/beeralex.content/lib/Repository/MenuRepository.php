<?php

namespace Beeralex\Content\Repository;

use Beeralex\Core\Repository\IblockRepository;

class MenuRepository extends IblockRepository
{
    public function __construct()
    {
        parent::__construct('menu');
    }
}
