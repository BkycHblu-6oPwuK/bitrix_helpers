<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;

/**
 * Справочники служб доставки (ТК) и тарифов ApiShip.
 *
 * Используется обработчиком доставки для наполнения профилей (гибридная схема,
 * см. docs/DECISIONS.md ADR-004): профиль хранит providerKey/tariffId, а список
 * доступных ТК/тарифов подтягивается динамически отсюда.
 */
final class ProviderService implements ProviderServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getProviders(array $filter = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->lists()->getProviders(
                100,
                0,
                $filter ? json_encode($filter, JSON_UNESCAPED_UNICODE) : ''
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

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getTariffs(array $filter = []): array
    {
        try {
            $response = $this->clientFactory->getClient()->listsExtended()->getTariffs(100, 0, $filter);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        return (array)($response->get('rows') ?? []);
    }
}
