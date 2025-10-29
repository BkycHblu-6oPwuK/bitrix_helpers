<?php
namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\DataManager;

class UserNotificationPreferenceTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName()
    {
        return 'beeralex_user_notification_preferences';
    }

    public static function getMap()
    {
        return [
            new IntegerField('USER_ID', ['primary' => true]),
            new IntegerField('NOTIFICATION_TYPE_ID', ['primary' => true]),
            new IntegerField('CHANNEL_ID', ['primary' => true]),
            new BooleanField('ENABLED', [
                'values' => ['N', 'Y'],
                'default_value' => 'Y'
            ]),

            new Reference(
                'TYPE',
                NotificationTypeTable::class,
                ['=this.NOTIFICATION_TYPE_ID' => 'ref.ID']
            ),

            new Reference(
                'CHANNEL',
                NotificationChannelTable::class,
                ['=this.CHANNEL_ID' => 'ref.ID']
            ),
        ];
    }
}
