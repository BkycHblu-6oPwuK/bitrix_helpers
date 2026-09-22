<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Request\CourierCallRequest;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\CourierServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Service\Support\RequestHydrator;

/**
 * Вызов курьера через ApiShip.
 */
final class CourierService implements CourierServiceContract
{
    use RequestHydrator;

    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    /**
     * @param array<string,mixed> $data Поля CourierCallRequest (providerKey, date, timeStart,
     *   timeEnd, weight/width/height/length, orderIds, адрес отправителя и контакты).
     * @return array<string,mixed>
     */
    public function call(array $data): array
    {
        $request = $this->hydrate(new CourierCallRequest(), $data);

        try {
            $response = $this->clientFactory->getClient()->courierCall()->create($request);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return [
            'id'              => $response->getId(),
            'created'         => $response->getCreated(),
            'providerNumber'  => $response->getProviderNumber(),
            'error'           => $response->getError(),
        ];
    }

    public function cancel(int $courierCallId): bool
    {
        try {
            $this->clientFactory->getClient()->courierCall()->cancel($courierCallId);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return true;
    }
}
