<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Response\StatusResponse;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\StatusServiceContract;
use Beeralex\Apiship\Dto\StatusDto;
use Beeralex\Apiship\Exception\ApiShipException;

/**
 * Статусы заказов ApiShip.
 *
 * logStatus() пишет в beeralex_apiship_status_log (Фаза 4, Repository\StatusLogRepository).
 * До появления репозитория метод только сообщает "изменился ли статус" в рамках вызова.
 */
final class StatusService implements StatusServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    public function getStatus(int $apiShipOrderId): StatusDto
    {
        try {
            $response = $this->clientFactory->getClient()->orders()->getStatusByOrderId($apiShipOrderId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return $this->toDto($apiShipOrderId, $response);
    }

    /**
     * @param int[] $apiShipOrderIds
     * @return StatusDto[]
     */
    public function getStatuses(array $apiShipOrderIds): array
    {
        try {
            $response = $this->clientFactory->getClient()->orders()->getStatuses($apiShipOrderIds);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $result = [];
        foreach ($response->getSucceedOrders() ?? [] as $succeedOrder) {
            $status = $succeedOrder->getStatus();
            $orderInfo = $succeedOrder->getOrderInfo();
            $result[] = StatusDto::make([
                'orderId' => $orderInfo?->getOrderId(),
                'key'     => $status?->getKey(),
                'name'    => $status?->getName(),
                'date'    => $status?->getCreated(),
            ]);
        }

        return $result;
    }

    public function logStatus(int $bitrixOrderId, StatusDto $status): bool
    {
        $repositoryClass = 'Beeralex\\Apiship\\Repository\\StatusLogRepository';

        if (!class_exists($repositoryClass)) {
            // Репозиторий появится в Фазе 4 — до этого просто подтверждаем "новый статус".
            return true;
        }

        return \service($repositoryClass)->logIfChanged($bitrixOrderId, $status);
    }

    private function toDto(int $apiShipOrderId, StatusResponse $response): StatusDto
    {
        $status = $response->getStatus();

        return StatusDto::make([
            'orderId' => $apiShipOrderId,
            'key'     => $status?->getKey(),
            'name'    => $status?->getName(),
            'date'    => $status?->getCreated(),
        ]);
    }
}
