<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Response\Part\Lists\Point;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\PointServiceContract;
use Beeralex\Apiship\Dto\PointDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\QueryFilterBuilder;
use Bitrix\Main\Data\Cache;
use Bitrix\Sale\Order;

final class PointService implements PointServiceContract
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Options $options,
    ) {}

    /**
     * @return array{items: PointDto[], total: int}
     */
    public function getPoints(array $filter = [], int $limit = 50, int $offset = 0): array
    {
        $cache = null;
        if ($this->options->cacheEnabled) {
            $cache = Cache::createInstance();
            $cacheKey = 'points_' . md5(serialize([$filter, $limit, $offset]));
            if ($cache->initCache($this->options->cacheTtl, $cacheKey, '/beeralex/apiship/points')) {
                return $cache->getVars();
            }
            $cache->startDataCache();
        }

        $filterStr = QueryFilterBuilder::build($filter);

        try {
            $response = $this->clientFactory->getClient()->lists()->getPoints($limit, $offset, $filterStr);
        } catch (ResponseException $e) {
            throw new ApiShipException($e->getErrorMessage() ?: $e->getMessage(), (int)$e->getCode(), $e);
        }

        $raw = is_string($response->getOriginJson())
            ? json_decode($response->getOriginJson(), true)
            : [];
        $total = (int)(($raw['meta']['total'] ?? $raw['meta']['count'] ?? 0));

        $items = [];
        foreach ($response->getResults() ?? [] as $point) {
            $items[] = $this->toDto($point);
        }

        $result = ['items' => $items, 'total' => $total];

        if ($cache !== null) {
            $cache->endDataCache($result);
        }

        return $result;
    }

    public function getPoint(string $id): ?PointDto
    {
        $result = $this->getPoints(['id' => $id], 1, 0);
        return $result['items'][0] ?? null;
    }

    /**
     * Загружает заказ, сохраняет свойство и заказ с указанным пунктом самовывоза
     * @throws \Throwable В случае ошибки при загрузке или сохранении заказа.
     */
    public function setPickupPoint(int $bitrixOrderId, string $pointId): bool
    {
        $order = Order::load($bitrixOrderId);
        if ($order === null) {
            return false;
        }

        $result = $this->setPickupPointOrderProperty($order, $this->options->propPickupPointId, $pointId);
        return $result && $order->save()->isSuccess();
    }

    /**
     * Устанавливает значение свойства заказа с указанным кодом на указанный пункт самовывоза.
     * @throws \Throwable В случае ошибки при установке значения свойства заказа.
     * @return bool Результат установки значения свойства заказа.
     */
    public function setPickupPointOrderProperty(Order $order, string $propCode, string $pointId): bool
    {
        $properties = $order->getPropertyCollection();
        $prop = $properties->getItemByOrderPropertyCode($propCode);
        if ($prop === null) {
            return false;
        }

        $result = $prop->setValue($pointId);
        return $result->isSuccess();
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
            'id'                 => (string)$point->getId(),
            'providerKey'        => (string)$point->getProviderKey(),
            'code'               => (string)$point->getCode(),
            'name'               => (string)$point->getName(),
            'address'            => implode(', ', array_filter([$point->getCity(), implode(' ', $addressParts)])),
            'city'               => (string)$point->getCity(),
            'cityGuid'           => (string)$point->getCityGuid(),
            'postIndex'          => (string)$point->getPostIndex(),
            'phone'              => (string)$point->getPhone(),
            'type'               => (string)$point->getType(),
            'availableOperation' => (array)$point->getAvailableOperation(),
            'cod'                => $point->getCod(),
            'paymentCash'        => $point->getPaymentCash(),
            'paymentCard'        => $point->getPaymentCard(),
            'fittingRoom'        => $point->getFittingRoom(),
            'workTime'           => $point->getWorktime(),
            'lat'                => $point->getLat() !== null ? (float)$point->getLat() : null,
            'lng'                => $point->getLng() !== null ? (float)$point->getLng() : null,
        ]);
    }
}
