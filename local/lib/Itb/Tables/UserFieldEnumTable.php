<?php
namespace Itb\Tables;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\UserFieldTable;

class UserFieldEnumTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_user_field_enum';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new IntegerField('USER_FIELD_ID'),
            new StringField('VALUE'),
            new StringField('DEF'),
            new StringField('SORT'),
            new StringField('XML_ID'),

            new Reference(
                'USER_FIELD',
                UserFieldTable::class,
                ['=this.USER_FIELD_ID' => 'ref.ID']
            ),
        ];
    }
}
