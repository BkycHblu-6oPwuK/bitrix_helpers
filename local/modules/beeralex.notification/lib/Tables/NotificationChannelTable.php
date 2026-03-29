<?php
namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\Type\DateTime;

class NotificationChannelTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName()
    {
        return 'beeralex_notification_channels';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new StringField('CODE', ['required' => true, 'unique' => true]),
            new StringField('NAME', ['required' => true]),
            new BooleanField('ACTIVE', [
                'values' => ['N', 'Y'],
                'default' => 'Y',
            ]),

            new DatetimeField('CREATED_AT', [
                'default' => function () {
                    return new DateTime();
                },
            ]),

            new Reference(
                'PREFERENCES',
                UserNotificationPreferenceTable::class,
                ['=this.ID' => 'ref.CHANNEL_ID']
            ),
        ];
    }
}
