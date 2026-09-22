<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Model;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Журнал входящих вебхуков ApiShip.
 *
 * EVENT_ID уникален (UNIQUE-индекс через install/index.php SQL, либо проверка перед вставкой
 * в WebhookLogRepository) — реализует идемпотентность обработки (ADR-007).
 */
class WebhookLogTable extends ORM\Data\DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_apiship_webhook_log';
    }

    public static function getMap(): array
    {
        return [
            new ORM\Fields\IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),

            new ORM\Fields\StringField('EVENT_ID', [
                'required' => true,
                'size'     => 128,
            ]),

            new ORM\Fields\StringField('EVENT_TYPE', [
                'required' => false,
                'size'     => 64,
            ]),

            new ORM\Fields\IntegerField('APISHIP_ORDER_ID', [
                'required' => false,
            ]),

            new ORM\Fields\TextField('RAW_BODY', [
                'required' => false,
            ]),

            new ORM\Fields\DatetimeField('CREATED_AT', [
                'default_value' => fn() => new Type\DateTime(),
            ]),
        ];
    }
}
