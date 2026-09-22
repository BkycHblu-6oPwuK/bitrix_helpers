<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Разобранное событие входящего вебхука ApiShip.
 *
 * Реальная структура события ApiShip варьируется по типу (orderStatus и др.);
 * DTO хранит все поля «как есть» и предоставляет удобные геттеры для основных.
 *
 * @property-read string $eventId Идентификатор события для дедупликации
 * @property-read string $type
 * @property-read int $orderId
 */
final class WebhookEventDto extends Resource
{
    public function getEventId(): string
    {
        // ApiShip не всегда присылает явный id события — используем tracing-id/uuid,
        // либо составной ключ (orderId+status+date) как fallback в WebhookService.
        return (string)($this->getString('id') ?? $this->getString('uuid') ?? '');
    }

    public function getType(): string
    {
        return (string)$this->getString('type');
    }

    public function getOrderId(): int
    {
        return (int)$this->getInt('orderId');
    }

    public function getStatusKey(): ?string
    {
        return $this->getString('status') ?? $this->getString('statusKey');
    }
}
