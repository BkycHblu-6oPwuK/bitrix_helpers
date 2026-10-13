<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\Model\WebhookLogTable;
use Bitrix\Main\SystemException;
use Beeralex\Core\Repository\Repository;

/**
 * Репозиторий журнала входящих вебхуков (таблица beeralex_apiship_webhook_log).
 *
 * Реализует идемпотентность (ADR-007): EVENT_ID уникален на уровне БД (unique-поле в
 * WebhookLogTable), поэтому повторная вставка того же события завершится ошибкой —
 * это и есть признак дубликата.
 */
final class WebhookLogRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(WebhookLogTable::class);
    }

    /**
     * Регистрирует событие. Возвращает true, если событие новое (успешно записано),
     * false — если это дубликат (уже было записано ранее).
     * @throws SystemException
     */
    public function registerEvent(WebhookEventDto $event, string $rawBody): bool
    {
        $eventId = $this->resolveEventId($event, $rawBody);

        if ($this->exists($eventId)) {
            return false;
        }

        $result = $this->add([
            'EVENT_ID'         => $eventId,
            'EVENT_TYPE'       => $event->type,
            'APISHIP_ORDER_ID' => $event->orderId ?: null,
            'RAW_BODY'         => $rawBody,
        ]);

        return $result > 0;
    }

    private function exists(string $eventId): bool
    {
        return $this->one(['=EVENT_ID' => $eventId], ['ID']) !== null;
    }

    /**
     * ApiShip не всегда присылает явный id события — если его нет, строим составной ключ
     * из типа+orderId+статуса+хэша тела, чтобы дедупликация всё равно работала.
     */
    private function resolveEventId(WebhookEventDto $event, string $rawBody): string
    {
        $eventId = $event->eventId;
        if ($eventId !== '') {
            return $eventId;
        }

        return $event->type . ':' . $event->orderId . ':' . $event->statusKey . ':' . md5($rawBody);
    }
}
