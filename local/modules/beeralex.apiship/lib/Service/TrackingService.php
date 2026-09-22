<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\TrackingServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;

/**
 * История статусов заказа для отображения покупателю (трекинг).
 */
final class TrackingService implements TrackingServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    public function getHistory(int $apiShipOrderId): array
    {
        try {
            $response = $this->clientFactory->getClient()->orders()->getStatusHistory($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $decoded = json_decode((string)$response->getOriginJson(), true);
        return (array)($decoded['rows'] ?? []);
    }
}
