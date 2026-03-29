<?php
namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\Mail\Internal\EventTable;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;
use Bitrix\MessageService\Internal\Entity\MessageTable;

class NotificationsTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName()
    {
        return 'beeralex_notifications';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new IntegerField('B_EVENT_ID', ['default' => null]),
            new IntegerField('B_MESSAGESERVICE_ID', ['default' => null]),
            new IntegerField('CODE_ID', ['default' => null]),
            new StringField('CHANNEL', [
                'required' => true,
                'size' => 50,
            ]),
            new StringField('RECIPIENT', [
                'required' => false,
                'size' => 255,
            ]),
            new TextField('BODY', ['default' => null]),
            new StringField('STATUS', [
                'default' => 'NEW',
                'size' => 50,
            ]),
            new DatetimeField('CREATED', ['default' => new DateTime()]),
            new DatetimeField('UPDATED_AT', ['default' => function () { return new DateTime(); }]),

            new Reference(
                'B_EVENT',
                EventTable::class,
                ['=this.B_EVENT_ID' => 'ref.ID']
            ),
            new Reference(
                'B_EVENT',
                MessageTable::class,
                ['=this.B_MESSAGESERVICE_ID' => 'ref.ID']
            ),
            new Reference(
                'CODE',
                NotificationCodeTable::class,
                ['=this.CODE_ID' => 'ref.ID']
            ),
        ];
    }
}
