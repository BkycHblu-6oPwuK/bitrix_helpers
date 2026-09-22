<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\Model\WebhookLogTable;

/**
 * Репозиторий журнала входящих вебхуков (таблица beeralex_apiship_webhook_log).
 *
 * Реализует идемпотентность (ADR-007): EVENT_ID уникален на уровне БД (unique-поле в
 * WebhookLogTable), поэтому повторная вставка того же события завершится ошибкой —
 * это и есть признак дубликата.
 */
final class WebhookLogRepository
{
    /**
     * Регистрирует событие. Возвращает true, если событие новое (успешно записано),
     * false — если это дубликат (уже было записано ранее).
     */
    public function registerEvent(WebhookEventDto $event, string $rawBody): bool
    {
        $eventId = $this->resolveEventId($event, $rawBody);

        if ($this->exists($eventId)) {
            return false;
        }

        $result = WebhookLogTable::add([
            'EVENT_ID'         => $eventId,
            'EVENT_TYPE'       => $event->getType(),
            'APISHIP_ORDER_ID' => $event->getOrderId() ?: null,
            'RAW_BODY'         => $rawBody,
        ]);

        // Гонка (два одновременных запроса с одним EVENT_ID): уникальный индекс не даст
        // вставить дубликат — трактуем неуспех add() как "уже обработано".
        return $result->isSuccess();
    }

    private function exists(string $eventId): bool
    {
        return WebhookLogTable::getList([
            'select' => ['ID'],
            'filter' => ['=EVENT_ID' => $eventId],
            'limit'  => 1,
        ])->fetch() !== false;
    }

    /**
     * ApiShip не всегда присылает явный id события — если его нет, строим составной ключ
     * из типа+orderId+статуса+хэша тела, чтобы дедупликация всё равно работала.
     */
    private function resolveEventId(WebhookEventDto $event, string $rawBody): string
    {
        $eventId = $event->getEventId();
        if ($eventId !== '') {
            return $eventId;
        }

        return $event->getType() . ':' . $event->getOrderId() . ':' . $event->getStatusKey() . ':' . md5($rawBody);
    }
}
