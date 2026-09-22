<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

use Beeralex\Apiship\Dto\WebhookEventDto;

interface WebhookServiceContract
{
    /** Подписаться на событие (регистрация вебхука в ApiShip). */
    public function subscribe(string $url, string $type): array;

    /** Список зарегистрированных подписок. */
    public function list(): array;

    /** Удалить подписку. */
    public function unsubscribe(string $uuid): bool;

    /**
     * Обработать входящее событие вебхука: дедупликация + лог + смена статуса заказа.
     * Возвращает false, если событие уже было обработано (дубликат).
     */
    public function handle(WebhookEventDto $event, string $rawBody): bool;
}
