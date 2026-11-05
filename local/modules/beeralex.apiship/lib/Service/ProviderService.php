<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Support\ClientFactory;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Support\QueryFilterBuilder;

class ProviderService implements ProviderServiceContract
{
    public function __construct(
        protected readonly ClientFactory $clientFactory
    ) {}

    public function getProviders(int $limit = 20, int $offset = 0, array $filter = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->lists()->getProviders(
                $limit,
                $offset,
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

    public function getTariffs(int $limit = 100, int $offset = 0, array $filter = [], array $fields = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->listsExtended()->getTariffs($limit, $offset, $filter, $fields);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }
        return (array)($response->get('rows') ?? []);
    }
}
