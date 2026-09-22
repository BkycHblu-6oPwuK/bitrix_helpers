<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Расширенные методы справочников ApiShip, отсутствующие в штатном SDK.
 *
 * Штатный Apiship\Api\Lists уже покрывает providers/points/pointTypes/services.
 * Здесь добавлены: statuses, providerStatuses, tariffs, deliveryTypes, pickupTypes.
 */
class Lists extends AbstractExtendedApi
{
    /**
     * Справочник статусов ApiShip.
     *
     * @param array<string,mixed> $filter
     */
    public function getStatuses(int $limit = 100, int $offset = 0, array $filter = [], string $fields = ''): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        if ($filter) {
            $query['filter'] = json_encode($filter, JSON_UNESCAPED_UNICODE);
        }
        if ($fields !== '') {
            $query['fields'] = $fields;
        }
        return $this->requestGet('lists/statuses', $query);
    }

    /**
     * Соответствие статусов СД статусам сервиса.
     */
    public function getProviderStatuses(int $limit = 100, int $offset = 0, string $providerKey = ''): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        if ($providerKey !== '') {
            $query['providerKey'] = $providerKey;
        }
        return $this->requestGet('lists/providerStatuses', $query);
    }

    /**
     * Актуальные тарифы.
     *
     * @param array<string,mixed> $filter
     */
    public function getTariffs(int $limit = 100, int $offset = 0, array $filter = [], string $fields = ''): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        if ($filter) {
            $query['filter'] = json_encode($filter, JSON_UNESCAPED_UNICODE);
        }
        if ($fields !== '') {
            $query['fields'] = $fields;
        }
        return $this->requestGet('lists/tariffs', $query);
    }

    /**
     * Типы доставки (до двери / до ПВЗ).
     */
    public function getDeliveryTypes(): RawResponse
    {
        return $this->requestGet('lists/deliveryTypes');
    }

    /**
     * Типы приёма.
     */
    public function getPickupTypes(): RawResponse
    {
        return $this->requestGet('lists/pickupTypes');
    }
}
