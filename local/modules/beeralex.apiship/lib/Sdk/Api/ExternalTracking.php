<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Трекинг сторонних заказов (созданных не через ApiShip).
 *
 * OpenAPI: POST /externalTracking/orders, DELETE /externalTracking/orders/{orderId}.
 */
class ExternalTracking extends AbstractExtendedApi
{
    /**
     * Поставить внешние заказы на трекинг.
     *
     * @param array<int,array<string,mixed>> $orders Массив заказов для отслеживания.
     */
    public function track(array $orders): RawResponse
    {
        return $this->requestPost('externalTracking/orders', $orders);
    }

    /**
     * Снять заказ с трекинга.
     */
    public function delete(string $orderId): RawResponse
    {
        return $this->requestDelete('externalTracking/orders/' . rawurlencode($orderId));
    }
}
