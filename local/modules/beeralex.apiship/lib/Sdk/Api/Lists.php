<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;
use Beeralex\Apiship\Support\QueryFilterBuilder;

/**
 * Расширенные методы справочников ApiShip, отсутствующие в штатном SDK.
 */
class Lists extends AbstractExtendedApi
{
    /**
     * Справочник статусов ApiShip.
     * filter: query-filter (providerKey=cdek)
     */
    public function getStatuses(int $limit = 100, int $offset = 0, array $filter = [], array $fields = []): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        $filterStr = QueryFilterBuilder::build($filter);
        if ($filterStr !== '') {
            $query['filter'] = $filterStr;
        }
        if (!empty($fields)) {
            $query['fields'] = implode(',', $fields);
        }
        return $this->requestGet('lists/statuses', $query);
    }

    /** Соответствие статусов ТК статусам ApiShip. */
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
     * filter: query-filter (id=123;providerKey=cdek)
     */
    public function getTariffs(int $limit = 100, int $offset = 0, array $filter = [], array $fields = []): RawResponse
    {
        $query = ['limit' => $limit, 'offset' => $offset];
        $filterStr = QueryFilterBuilder::build($filter);
        if ($filterStr !== '') {
            $query['filter'] = $filterStr;
        }
        if (!empty($fields)) {
            $query['fields'] = implode(',', $fields);
        }
        return $this->requestGet('lists/tariffs', $query);
    }

    /** Типы доставки (до двери / до ПВЗ). */
    public function getDeliveryTypes(): RawResponse
    {
        return $this->requestGet('lists/deliveryTypes');
    }

    /** Типы приёма (от двери / привоз). */
    public function getPickupTypes(): RawResponse
    {
        return $this->requestGet('lists/pickupTypes');
    }

    /** Способы оплаты. */
    public function getPaymentMethods(): RawResponse
    {
        return $this->requestGet('lists/paymentMethods');
    }

    /** Типы операций для ПВЗ. */
    public function getOperationTypes(): RawResponse
    {
        return $this->requestGet('lists/operationTypes');
    }

    /** Параметры подключения конкретной ТК. */
    public function getProviderParams(string $providerKey): RawResponse
    {
        return $this->requestGet('lists/providers/' . rawurlencode($providerKey) . '/params');
    }

    /** Список городов ТК (boxberry/dpd/cdek). */
    public function getProviderCities(string $provider, int $limit = 100, int $offset = 0): RawResponse
    {
        $allowed = ['boxberry', 'dpd', 'cdek'];
        if (!in_array($provider, $allowed, true)) {
            throw new \InvalidArgumentException("provider must be one of: " . implode(', ', $allowed));
        }
        return $this->requestGet('lists/providerCities/' . $provider, ['limit' => $limit, 'offset' => $offset]);
    }

    /** Подключения ТК (списочный справочник для расчёта). */
    public function getProviderConnections(int $limit = 100, int $offset = 0): RawResponse
    {
        return $this->requestGet('lists/providers/connections', ['limit' => $limit, 'offset' => $offset]);
    }

    /** Конкретное подключение ТК. */
    public function getProviderConnection(int $id): RawResponse
    {
        return $this->requestGet('lists/providers/connections/' . $id);
    }
}
