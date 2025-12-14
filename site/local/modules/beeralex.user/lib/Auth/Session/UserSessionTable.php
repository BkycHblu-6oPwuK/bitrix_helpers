<?php

declare(strict_types=1);

namespace Beeralex\User\Auth\Session;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

class UserSessionTable extends ORM\Data\DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_user_session';
    }

    public static function getMap(): array
    {
        return [
            new ORM\Fields\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new ORM\Fields\IntegerField('USER_ID', [
                'required' => true,
            ]),

            new ORM\Fields\StringField('REFRESH_TOKEN', [
                'required' => true,
                'size' => 255,
            ]),

            new ORM\Fields\StringField('USER_AGENT', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('IP_ADDRESS', [
                'required' => false,
            ]),

            new ORM\Fields\DatetimeField('LAST_ACTIVITY', [
                'default_value' => fn() => new Type\DateTime(),
            ]),

            new ORM\Fields\DatetimeField('CREATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),

            new ORM\Fields\BooleanField('REVOKED', [
                'values' => ['N', 'Y'],
                'default_value' => 'N',
            ]),

            (new ORM\Fields\Relations\Reference(
                'USER',
                Main\UserTable::class,
                [
                    '=this.USER_ID' => 'ref.ID'
                ]
            ))->configureJoinType('LEFT'),
        ];
    }
}