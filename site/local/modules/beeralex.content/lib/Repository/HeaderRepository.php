<?php

namespace Beeralex\Content\Repository;

use Beeralex\Core\Repository\IblockRepository;

class HeaderRepository extends IblockRepository
{
    public function __construct()
    {
        parent::__construct('header');
    }
}
