<?php

namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Sms\TemplateTable;
use Bitrix\Main\Type\DateTime;

class NotificationTemplateLinkTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_notification_template_links';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new StringField('SMS_TEMPLATE_ID', [
                'required' => true,
            ]),

            new StringField('CHANNEL_ID', [
                'required' => true,
            ]),

            new BooleanField('ACTIVE', [
                'values' => ['N', 'Y'],
                'default' => 'Y',
            ]),

            new DatetimeField('CREATED_AT', [
                'default' => function () {
                    return new DateTime();
                },
            ]),

            new DatetimeField('UPDATED_AT', [
                'default' => function () {
                    return new DateTime();
                },
            ]),

            new Reference(
                'CHANNEL',
                NotificationChannelTable::class,
                ['=this.CHANNEL_ID' => 'ref.ID']
            ),

            new Reference(
                'SMS_TEMPLATE',
                TemplateTable::class,
                ['=this.SMS_TEMPLATE_ID' => 'ref.ID']
            ),
        ];
    }
}
