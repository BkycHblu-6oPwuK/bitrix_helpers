<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Model;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Связь заказа Bitrix <-> заказа ApiShip.
 *
 * Одна запись на пару (BITRIX_ORDER_ID, APISHIP_ORDER_ID). Заполняется при отправке заказа
 * в ApiShip (OrderService::createForBitrixOrder, событие OnSaleStatusOrderChange, ADR-005).
 */
class OrderTable extends ORM\Data\DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_apiship_order';
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
                'required' => true,
            ]),

            new ORM\Fields\StringField('PROVIDER_KEY', [
                'required' => false,
                'size'     => 32,
            ]),

            new ORM\Fields\IntegerField('TARIFF_ID', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('LAST_STATUS_KEY', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\DatetimeField('CREATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),

            new ORM\Fields\DatetimeField('UPDATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),
        ];
    }
}
