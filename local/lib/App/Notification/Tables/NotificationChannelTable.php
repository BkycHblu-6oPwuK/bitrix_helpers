<?php
namespace App\Notification\Tables;

use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Beeralex\Core\BaseTable;

class NotificationChannelTable extends BaseTable
{
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

            new Reference(
                'PREFERENCES',
                UserNotificationPreferenceTable::class,
                ['=this.ID' => 'ref.CHANNEL_ID']
            ),
        ];
    }
}
