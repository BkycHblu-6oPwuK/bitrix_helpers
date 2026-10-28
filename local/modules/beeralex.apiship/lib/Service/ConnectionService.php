<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Bitrix\Main\Data\Cache;

final class ConnectionService implements ConnectionServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Options $options,
    ) {}

    /**
     * @return array<int,array<string,mixed>>
     * @throws ApiShipException
     */
    public function getConnections(array $filter = [], int $limit = 100, int $offset = 0): array
    {
        $cache = null;
        if ($this->options->cacheEnabled) {
            $cache = Cache::createInstance();
            $cacheKey = 'connections_' . md5(serialize([$filter, $limit, $offset]));
            if ($cache->initCache($this->options->cacheTtl, $cacheKey, '/beeralex/apiship')) {
                return $cache->getVars();
            }
            $cache->startDataCache();
        }

        try {
            $response = $this->clientFactory->getClient()->connections()->list($offset, $limit, $filter);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $rows = $response->get('rows');
        $result = is_array($rows) ? array_values($rows) : [];

        if ($cache !== null) {
            $cache->endDataCache($result);
        }

        return $result;
    }
}
