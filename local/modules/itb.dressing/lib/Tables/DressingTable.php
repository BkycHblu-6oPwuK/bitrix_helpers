<?php

namespace Itb\Dressing\Tables;

use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\Type\DateTime;
use Itb\Core\BaseTable;

class DressingTable extends BaseTable
{
    public static function getTableName()
    {
        return 'itb_dressing';
    }

    public static function getMap()
    {
        return [
            'ID'          => new IntegerField('ID', [
                'autocomplete' => true,
                'primary'      => true,
            ]),
            'FUSER_ID'    => new IntegerField('FUSER_ID', [
                'required' => true,
            ]),
            'OFFER_ID'  => new IntegerField('OFFER_ID', [
                'required' => true,
            ]),
            'INSERT_TIME' => new DatetimeField('INSERT_TIME', [
                'default_value' => new DateTime(),
            ]),
        ];
    }
}
