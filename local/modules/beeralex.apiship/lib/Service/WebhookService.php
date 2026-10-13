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

/**
 * Подписки на вебхуки и обработка входящих событий ApiShip.
 *
 * Обрабатываются 3 типа событий (все несут orderInfo; plannedDelivery — внутри orderInfo).
 * Данные КАЖДОГО события сохраняются в beeralex_apiship_order общим шагом updateFromWebhook():
 *  - ORDER_STATUS                 — снимок orderInfo + статуса; плюс строка в истории статусов
 *                                   и применение статуса к заказу/отгрузке Bitrix;
 *  - PLANNED_DELIVERY_DATE_UPDATE — снимок плановой даты доставки + флаг PLANNED_DELIVERY_CHANGED;
 *  - ORDER_NUMBER_UPDATE          — снимок номера заказа (providerNumber/clientNumber).
 *
 * handle() реализует идемпотентность (ADR-007): дедупликация по eventId через
 * beeralex_apiship_webhook_log (Repository\WebhookLogRepository).
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
        $connection = Application::getInstance()->getConnection();

        try {
            $isNew = \service(WebhookLogRepository::class)->registerEvent($event, $rawBody);
            if (!$isNew) {
                $connection->commitTransaction();
                return false;
            }

            $apiShipOrderId = $event->orderId;
            if ($apiShipOrderId <= 0) {
                $connection->commitTransaction();
                return true;
            }

            $bitrixOrderId = \service(OrderRepository::class)->getBitrixOrderId($apiShipOrderId);

            \service(OrderRepository::class)->updateFromWebhook(
                $apiShipOrderId,
                $this->buildOrderFields($event)
            );

            if ($event->isOrderStatus) {
                $this->handleOrderStatus($event, $bitrixOrderId);
            }

            $connection->commitTransaction();
            return true;
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
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
        \service(StatusLogRepository::class)->addFromEvent($event, $bitrixOrderId);

        $statusKey = $event->statusKey;
        if ($bitrixOrderId !== null && $statusKey !== null) {
            \service(StatusMapper::class)->applyToBitrixOrder($bitrixOrderId, $statusKey);
        }
    }
}
