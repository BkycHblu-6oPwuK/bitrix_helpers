<?php
namespace Beeralex\Notification\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Type\DateTime;

class NotificationCodeTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName()
    {
        return 'beeralex_verification_codes';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            // К какому пользователю привязан код (если есть)
            new IntegerField('USER_ID', ['default' => null]),

            // Канал отправки: sms / email
            new StringField('CHANNEL', [
                'required' => true,
                'size' => 50,
            ]),

            // Получатель (телефон / email)
            new StringField('RECIPIENT', [
                'required' => true,
                'size' => 255,
            ]),

            // Сам код
            new StringField('CODE', [
                'required' => true,
                'size' => 20,
            ]),

            // Цель (например, "registration", "password_reset")
            new StringField('PURPOSE', [
                'required' => false,
                'size' => 50,
            ]),

            // Флаг использования
            new BooleanField('USED', ['values' => ['N', 'Y'], 'default' => 'N']),

            // Срок действия
            new DatetimeField('EXPIRES_AT', [
                'required' => true,
            ]),

            // Дата создания
            new DatetimeField('CREATED_AT', [
                'default' => function () {
                    return new DateTime();
                },
            ]),

            new Reference(
                'NOTIFICATION',
                UserNotificationPreferenceTable::class,
                ['=this.ID' => 'ref.CODE_ID']
            ),
        ];
    }
}
