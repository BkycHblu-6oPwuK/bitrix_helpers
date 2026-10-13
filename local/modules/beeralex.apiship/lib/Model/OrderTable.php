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

            // --- Снимок запроса создания (POST /orders), пишется в OrderService::create ---

            new ORM\Fields\IntegerField('PROVIDER_CONNECT_ID', [
                'required' => false,
            ]),

            new ORM\Fields\IntegerField('PICKUP_TYPE', [
                'required' => false,
            ]),

            new ORM\Fields\IntegerField('DELIVERY_TYPE', [
                'required' => false,
            ]),

            new ORM\Fields\IntegerField('POINT_IN_ID', [
                'required' => false,
            ]),

            new ORM\Fields\IntegerField('POINT_OUT_ID', [
                'required' => false,
            ]),

            new ORM\Fields\IntegerField('WEIGHT', [
                'required' => false,
            ]),

            new ORM\Fields\FloatField('ASSESSED_COST', [
                'required' => false,
            ]),

            new ORM\Fields\FloatField('COD_COST', [
                'required' => false,
            ]),

            new ORM\Fields\FloatField('DELIVERY_COST', [
                'required' => false,
            ]),

            new ORM\Fields\DateField('PICKUP_DATE', [
                'required' => false,
            ]),

            new ORM\Fields\DateField('DELIVERY_DATE', [
                'required' => false,
            ]),

            new ORM\Fields\DatetimeField('APISHIP_CREATED_AT', [
                'required' => false,
            ]),

            // Полный JSON запроса (sender/recipient/places/extraParams и т.д.)
            new ORM\Fields\TextField('REQUEST_JSON', [
                'required' => false,
            ]),

            // --- Снимок данных из вебхуков ApiShip (orderInfo) ---

            new ORM\Fields\StringField('PROVIDER_NUMBER', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\StringField('CLIENT_NUMBER', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\StringField('BARCODE', [
                'required' => false,
                'size'     => 128,
            ]),

            new ORM\Fields\StringField('ADDITIONAL_PROVIDER_NUMBER', [
                'required' => false,
                'size'     => 128,
            ]),

            new ORM\Fields\StringField('RETURN_PROVIDER_NUMBER', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\TextField('CHECK_URL', [
                'required' => false,
            ]),

            new ORM\Fields\TextField('TRACKING_URL', [
                'required' => false,
            ]),

            // --- Снимок последнего статуса (событие ORDER_STATUS) ---

            new ORM\Fields\StringField('STATUS_KEY', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\StringField('STATUS_NAME', [
                'required' => false,
                'size'     => 255,
            ]),

            new ORM\Fields\DatetimeField('STATUS_DATE', [
                'required' => false,
            ]),

            // --- Плановая дата доставки (истории не ведём, только текущее значение + флаг) ---

            new ORM\Fields\DateField('PLANNED_DELIVERY_DATE', [
                'required' => false,
            ]),

            new ORM\Fields\StringField('PLANNED_DELIVERY_TIME_START', [
                'required' => false,
                'size'     => 5,
            ]),

            new ORM\Fields\StringField('PLANNED_DELIVERY_TIME_END', [
                'required' => false,
                'size'     => 5,
            ]),

            new ORM\Fields\BooleanField('PLANNED_DELIVERY_CHANGED', [
                'values'        => ['N', 'Y'],
                'default_value' => 'N',
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
