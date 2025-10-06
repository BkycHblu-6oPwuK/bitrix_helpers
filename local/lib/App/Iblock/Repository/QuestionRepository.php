<?php

namespace App\Iblock\Repository;

use Beeralex\Core\Helpers\IblockHelper;

class QuestionRepository
{
    /**
     * @var \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = IblockHelper::getElementApiTableByCode('questions');
    }

    public function getQuestions(array $select = ['NAME', 'PREVIEW_TEXT'], array $filter = ['ACTIVE' => 'Y'], array $sort = ['SORT' =>'ASC']) : array
    {
        return $this->entity::query()->setSelect($select)->setFilter($filter)->setOrder($sort)->setCacheTtl(86400)->exec()->fetchAll();
    }
}
