<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\WebhookServiceContract;
use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Repository\StatusLogRepository;
use Beeralex\Apiship\Repository\WebhookLogRepository;
use Beeralex\Apiship\StatusMapper;
use Bitrix\Main\Application;
final class WebhookService implements WebhookServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly WebhookLogRepository $webhookLogRepository,
        private readonly OrderRepository $orderRepository,
        private readonly StatusLogRepository $statusLogRepository,
        private readonly StatusMapper $statusMapper,
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
        $connection = Application::getInstance()->getConnection();
        $connection->startTransaction();

        try {
            $isNew = $this->webhookLogRepository->registerEvent($event, $rawBody);
            if (!$isNew) {
                $connection->commitTransaction();
                return false;
            }

            $apiShipOrderId = $event->orderId;
            if ($apiShipOrderId <= 0) {
                $this->webhookLogRepository->markProcessed($event, $rawBody);
                $connection->commitTransaction();
                return true;
            }

            $bitrixOrderId = $this->orderRepository->getBitrixOrderId($apiShipOrderId);

            $this->orderRepository->updateFromWebhook(
                $apiShipOrderId,
                $this->buildOrderFields($event)
            );

            if ($event->isOrderStatus) {
                $this->handleOrderStatus($event, $bitrixOrderId);
            }

            $this->webhookLogRepository->markProcessed($event, $rawBody);
            $connection->commitTransaction();
            return true;
        } catch (\Throwable $e) {
            $connection->rollbackTransaction();
            $this->webhookLogRepository->markFailed($event, $rawBody, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Снимок полей заказа из вебхука для beeralex_apiship_order (сырые значения; конвертацию
     * дат и логику флага изменения даты делает OrderRepository::updateFromWebhook).
     *
     * @return array<string,mixed>
     */
    private function buildOrderFields(WebhookEventDto $event): array
    {
        return [
            'PROVIDER_NUMBER'             => $event->providerNumber,
            'CLIENT_NUMBER'              => $event->clientNumber,
            'BARCODE'                   => $event->barcode,
            'ADDITIONAL_PROVIDER_NUMBER' => $event->additionalProviderNumber,
            'RETURN_PROVIDER_NUMBER'    => $event->returnProviderNumber,
            'CHECK_URL'                 => $event->checkUrl,
            'TRACKING_URL'              => $event->trackingUrl,
            'PROVIDER_KEY'              => $event->providerKey,
            'STATUS_KEY'                => $event->statusKey,
            'STATUS_NAME'               => $event->statusName,
            'STATUS_DATE'               => $event->statusCreated,
            'PLANNED_DELIVERY_DATE'      => $event->plannedDeliveryDate,
            'PLANNED_DELIVERY_TIME_START' => $event->plannedDeliveryTimeStart,
            'PLANNED_DELIVERY_TIME_END'  => $event->plannedDeliveryTimeEnd,
        ];
    }

    /**
     * ORDER_STATUS: пишем строку в историю статусов и применяем статус к заказу/отгрузке Bitrix.
     */
    private function handleOrderStatus(WebhookEventDto $event, ?int $bitrixOrderId): void
    {
        $this->statusLogRepository->addFromEvent($event, $bitrixOrderId);

        $statusKey = $event->statusKey;
        if ($bitrixOrderId !== null && $statusKey !== null) {
            $this->statusMapper->applyToBitrixOrder($bitrixOrderId, $statusKey);
        }
    }
}
