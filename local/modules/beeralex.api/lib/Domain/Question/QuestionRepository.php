<?php

namespace App\Iblock\Repository;

use Beeralex\Core\Service\IblockService;

class QuestionRepository
{
    /**
     * @var \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = service(IblockService::class)->getElementApiTableByCode('questions');
    }

    public function getQuestions(array $select = ['NAME', 'PREVIEW_TEXT'], array $filter = ['ACTIVE' => 'Y'], array $sort = ['SORT' =>'ASC']) : array
    {
        return $this->entity::query()->setSelect($select)->setFilter($filter)->setOrder($sort)->setCacheTtl(86400)->exec()->fetchAll();
    }
}
