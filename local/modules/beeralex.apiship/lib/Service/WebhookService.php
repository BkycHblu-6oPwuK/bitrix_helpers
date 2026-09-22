<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\WebhookServiceContract;
use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\StatusMapper;

/**
 * Подписки на вебхуки и обработка входящих событий ApiShip.
 *
 * handle() реализует идемпотентность (ADR-007): дедупликация по eventId через
 * beeralex_apiship_webhook_log (Фаза 4, Repository\WebhookLogRepository).
 */
final class WebhookService implements WebhookServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    public function subscribe(string $url, string $type): array
    {
        $response = $this->clientFactory->getClient()->webhooks()->subscribe($url, $type);
        return $response->toArray();
    }

    public function list(): array
    {
        $response = $this->clientFactory->getClient()->webhooks()->list();
        return $response->toArray();
    }

    public function unsubscribe(string $uuid): bool
    {
        $this->clientFactory->getClient()->webhooks()->delete($uuid);
        return true;
    }

    public function handle(WebhookEventDto $event, string $rawBody): bool
    {
        $logRepositoryClass = 'Beeralex\\Apiship\\Repository\\WebhookLogRepository';
        $orderRepositoryClass = 'Beeralex\\Apiship\\Repository\\OrderRepository';

        // Дедупликация: если репозиторий ещё не создан (ранние фазы), считаем событие новым.
        if (class_exists($logRepositoryClass)) {
            $isNew = \service($logRepositoryClass)->registerEvent($event, $rawBody);
            if (!$isNew) {
                return false;
            }
        }

        if (!class_exists($orderRepositoryClass) || $event->getStatusKey() === null) {
            return true;
        }

        $bitrixOrderId = \service($orderRepositoryClass)->getBitrixOrderId($event->getOrderId());
        if ($bitrixOrderId === null) {
            return true;
        }

        \service(StatusMapper::class)->applyToBitrixOrder($bitrixOrderId, $event->getStatusKey());

        return true;
    }
}
