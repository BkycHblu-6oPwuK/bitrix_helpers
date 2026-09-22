<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Model;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Журнал статусов заказов ApiShip.
 *
 * Одна запись на каждую фиксацию статуса (для истории/аудита и для проверки
 * "статус изменился" перед сменой STATUS_ID заказа Bitrix).
 */
class StatusLogTable extends ORM\Data\DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_apiship_status_log';
    }

    public static function getMap(): array
    {
        return [
            new ORM\Fields\IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),

            new ORM\Fields\IntegerField('BITRIX_ORDER_ID', [
                'required' => true,
            ]),

            new ORM\Fields\IntegerField('APISHIP_ORDER_ID', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('STATUS_KEY', [
                'required' => true,
                'size'     => 64,
            ]),

            new ORM\Fields\StringField('STATUS_NAME', [
                'required' => false,
                'size'     => 255,
            ]),

            new ORM\Fields\DatetimeField('STATUS_DATE', [
                'required' => false,
            ]),

            new ORM\Fields\DatetimeField('CREATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),
        ];
    }
}
