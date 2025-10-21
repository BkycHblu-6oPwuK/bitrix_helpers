<?php
namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * таблица связывающая тип уведомления с событиями битрикс
 */
class NotificationLinkEventTypeTable extends DataManager
{
    use TableManagerTrait;
    
    public static function getTableName()
    {
        return 'beeralex_notification_link_event';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new IntegerField('EVENT_ID', ['required' => true]),
            new IntegerField('EVENT_TYPE_ID', ['required' => true]),

            new Reference(
                'EVENT',
                EventTypeTable::class,
                ['=this.EVENT_ID' => 'ref.ID']
            ),
            new Reference(
                'EVENT_TYPE',
                NotificationTypeTable::class,
                ['=this.EVENT_TYPE_ID' => 'ref.ID']
            ),
        ];
    }
}
