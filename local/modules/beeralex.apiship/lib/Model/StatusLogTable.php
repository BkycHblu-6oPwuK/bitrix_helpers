<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Model;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * История изменения статусов заказов ApiShip (таблица beeralex_apiship_status_log).
 *
 * Одна строка на каждое событие вебхука ORDER_STATUS (см. WebhookService::handleOrderStatus).
 * Историю переносов плановой даты доставки здесь НЕ ведём — только статусы.
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

            new ORM\Fields\IntegerField('APISHIP_ORDER_ID', [
                'required' => true,
            ]),

            new ORM\Fields\IntegerField('BITRIX_ORDER_ID', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('STATUS_KEY', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\StringField('STATUS_NAME', [
                'required' => false,
                'size'     => 255,
            ]),

            new ORM\Fields\TextField('DESCRIPTION', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('ERROR_CODE', [
                'required' => false,
                'size'     => 16,
            ]),

            new ORM\Fields\StringField('PROVIDER_CODE', [
                'required' => false,
                'size'     => 128,
            ]),

            new ORM\Fields\StringField('PROVIDER_NAME', [
                'required' => false,
                'size'     => 255,
            ]),

            new ORM\Fields\TextField('PROVIDER_DESCRIPTION', [
                'required' => false,
            ]),

            // Когда статус создан в системе СД.
            new ORM\Fields\DatetimeField('CREATED_PROVIDER', [
                'required' => false,
            ]),

            // Когда статус получен на стороне ApiShip.
            new ORM\Fields\DatetimeField('CREATED', [
                'required' => false,
            ]),

            // Когда строка записана нами.
            new ORM\Fields\DatetimeField('CREATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),
        ];
    }
}
