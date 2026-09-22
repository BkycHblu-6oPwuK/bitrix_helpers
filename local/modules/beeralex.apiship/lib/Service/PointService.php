<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Response\Part\Lists\Point;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\PointServiceContract;
use Beeralex\Apiship\Dto\PointDto;
use Beeralex\Apiship\Exception\ApiShipException;

/**
 * Пункты выдачи/приёма заказов (ПВЗ) ApiShip.
 */
final class PointService implements PointServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    /**
     * @param array<string,mixed> $filter
     * @return PointDto[]
     */
    public function getPoints(array $filter = [], int $limit = 50, int $offset = 0): array
    {
        try {
            $response = $this->clientFactory->getClient()->lists()->getPoints(
                $limit,
                $offset,
                $filter ? json_encode($filter, JSON_UNESCAPED_UNICODE) : ''
            );
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $result = [];
        foreach ($response->getResults() ?? [] as $point) {
            $result[] = $this->toDto($point);
        }

        return $result;
    }

    public function getPoint(string $id): ?PointDto
    {
        $points = $this->getPoints(['id' => $id], 1, 0);
        return $points[0] ?? null;
    }

    private function toDto(Point $point): PointDto
    {
        $addressParts = array_filter([
            $point->getStreet(),
            $point->getHouse(),
            $point->getBlock(),
            $point->getOffice(),
        ]);

        return PointDto::make([
            'id'          => (string)$point->getId(),
            'providerKey' => (string)$point->getProviderKey(),
            'name'        => (string)$point->getName(),
            'address'     => implode(', ', array_filter([$point->getCity(), implode(' ', $addressParts)])),
            'lat'         => $point->getLat() !== null ? (float)$point->getLat() : null,
            'lng'         => $point->getLng() !== null ? (float)$point->getLng() : null,
        ]);
    }
}
