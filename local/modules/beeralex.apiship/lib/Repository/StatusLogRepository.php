<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\Model\StatusLogTable;
use Beeralex\Apiship\Support\DateHelper;
use Beeralex\Core\Repository\Repository;
use Bitrix\Main\SystemException;

/**
 * История изменения статусов заказов ApiShip (таблица beeralex_apiship_status_log).
 *
 * Одна строка на каждое событие ORDER_STATUS. Историю переносов плановой даты доставки
 * здесь НЕ ведём (по требованию) — только статусы.
 */
final class StatusLogRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(StatusLogTable::class);
    }

    /**
     * Добавляет запись в историю статусов на основе данных вебхука.
     * @throws SystemException
     */
    public function addFromEvent(WebhookEventDto $event, ?int $bitrixOrderId) : int
    {
        return $this->add([
            'APISHIP_ORDER_ID'     => $event->orderId,
            'BITRIX_ORDER_ID'      => $bitrixOrderId,
            'STATUS_KEY'           => $event->statusKey,
            'STATUS_NAME'          => $event->statusName,
            'DESCRIPTION'          => $event->statusDescription,
            'ERROR_CODE'           => $event->statusErrorCode,
            'PROVIDER_CODE'        => $event->statusProviderCode,
            'PROVIDER_NAME'        => $event->statusProviderName,
            'PROVIDER_DESCRIPTION' => $event->statusProviderDescription,
            'CREATED_PROVIDER'     => DateHelper::toDateTime($event->statusCreatedProvider),
            'CREATED'              => DateHelper::toDateTime($event->statusCreated),
        ]);
    }
}
