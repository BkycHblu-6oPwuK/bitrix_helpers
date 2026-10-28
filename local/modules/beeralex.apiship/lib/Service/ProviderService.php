<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Support\QueryFilterBuilder;

final class ProviderService implements ProviderServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    public function getProviders(array $filter = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->lists()->getProviders(
                999,
                0,
                QueryFilterBuilder::build($filter)
            );
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $result = [];
        foreach ($response->getResults() ?? [] as $provider) {
            $result[] = [
                'key'         => $provider->getKey(),
                'name'        => $provider->getName(),
                'description' => $provider->getDescription(),
            ];
        }

        return $result;
    }

    public function getTariffs(array $filter = [], array $fields = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->listsExtended()->getTariffs(999, 0, $filter, $fields);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
        return (array)($response->get('rows') ?? []);
    }
}
