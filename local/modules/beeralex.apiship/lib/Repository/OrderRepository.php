<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Repository;

use Beeralex\Apiship\Dto\CreateOrderDto;
use Bitrix\Main\SystemException;
use Beeralex\Apiship\Dto\OrderResultDto;
use Beeralex\Apiship\Model\OrderTable;
use Beeralex\Apiship\Support\DateHelper;
use Beeralex\Core\Repository\Repository;

/**
 * Репозиторий связи заказ Bitrix <-> заказ ApiShip (таблица beeralex_apiship_order).
 */
final class OrderRepository extends Repository
{
    public function __construct()
    {
        return parent::__construct(OrderTable::class);
    }

    /**
     * Создать (или обновить) связь и снимок запроса создания. Один заказ Bitrix — одна активная запись ApiShip.
     * @throws SystemException
     */
    public function saveCreated(CreateOrderDto $dto, OrderResultDto $result, string $requestJson): int
    {
        $fields = [
            'APISHIP_ORDER_ID'   => $result->orderId,
            'PROVIDER_KEY'       => $dto->providerKey,
            'TARIFF_ID'          => $dto->tariffId ?: null,
            'CLIENT_NUMBER'      => $dto->clientNumber,
            'PROVIDER_CONNECT_ID' => $dto->providerConnectId,
            'PICKUP_TYPE'        => $dto->pickupType,
            'DELIVERY_TYPE'      => $dto->deliveryType,
            'POINT_IN_ID'        => $dto->pointInId,
            'POINT_OUT_ID'       => $dto->pointOutId,
            'WEIGHT'             => $dto->weight,
            'ASSESSED_COST'      => $dto->cost['assessedCost'] ?? null,
            'COD_COST'           => $dto->cost['codCost'] ?? null,
            'DELIVERY_COST'      => $dto->cost['deliveryCost'] ?? null,
            'PICKUP_DATE'        => DateHelper::toDate($dto->pickupDate),
            'DELIVERY_DATE'      => DateHelper::toDate($dto->deliveryDate),
            'APISHIP_CREATED_AT' => DateHelper::toDateTime($result->created),
            'REQUEST_JSON'       => $requestJson,
        ];

        $existing = $this->one(['=BITRIX_ORDER_ID' => $dto->bitrixOrderId], ['ID']);
        $id = $existing !== null ? (int)$existing['ID'] : null;
        if ($id !== null) {
            $this->update($id, $fields + ['UPDATED_AT' => new \Bitrix\Main\Type\DateTime()]);
            return $id;
        }

        $id = $this->add($fields + ['BITRIX_ORDER_ID' => $dto->bitrixOrderId]);
        return $id;
    }

    public function getApiShipOrderId(int $bitrixOrderId): ?int
    {
        $row = $this->query()->setSelect(['APISHIP_ORDER_ID'])->where('BITRIX_ORDER_ID', $bitrixOrderId)->exec()->fetch();
        return $row ? (int)$row['APISHIP_ORDER_ID'] : null;
    }

    /**
     * Обратный поиск: по ID заказа ApiShip найти заказ Bitrix (нужен для обработки вебхуков).
     */
    public function getBitrixOrderId(int $apiShipOrderId): ?int
    {
        $row = $this->query()->setSelect(['BITRIX_ORDER_ID'])->where('APISHIP_ORDER_ID', $apiShipOrderId)->exec()->fetch();
        return $row ? (int)$row['BITRIX_ORDER_ID'] : null;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByApiShipOrderId(int $apiShipOrderId): ?array
    {
        return $this->one(['=APISHIP_ORDER_ID' => $apiShipOrderId]);
    }

    /**
     * Частичное обновление записи заказа данными из вебхука (только переданные непустые поля).
     *
     * Строку создаём только при отправке заказа (BITRIX_ORDER_ID обязателен), поэтому если
     * связи ещё нет — молча выходим (событие остаётся в webhook_log/status_log).
     *
     * Флаг PLANNED_DELIVERY_CHANGED выставляется в 'Y', когда пришла плановая дата, отличная
     * от уже сохранённой (первичное заполнение изменением не считаем).
     *
     * @param array<string,mixed> $fields сырые значения; STATUS_DATE/PLANNED_DELIVERY_DATE — строки.
     * @throws SystemException
     */
    public function updateFromWebhook(int $apiShipOrderId, array $fields): void
    {
        $row = $this->one(['=APISHIP_ORDER_ID' => $apiShipOrderId], ['ID', 'PLANNED_DELIVERY_DATE']);
        if ($row === null) {
            return;
        }

        $update = [];

        $stringColumns = [
            'PROVIDER_NUMBER', 'CLIENT_NUMBER', 'BARCODE', 'ADDITIONAL_PROVIDER_NUMBER',
            'RETURN_PROVIDER_NUMBER', 'CHECK_URL', 'TRACKING_URL', 'PROVIDER_KEY',
            'STATUS_KEY', 'STATUS_NAME', 'PLANNED_DELIVERY_TIME_START', 'PLANNED_DELIVERY_TIME_END',
        ];
        foreach ($stringColumns as $column) {
            if (isset($fields[$column]) && $fields[$column] !== '') {
                $update[$column] = $fields[$column];
            }
        }

        if (!empty($fields['STATUS_DATE'])) {
            $statusDate = DateHelper::toDateTime((string)$fields['STATUS_DATE']);
            if ($statusDate !== null) {
                $update['STATUS_DATE'] = $statusDate;
            }
        }

        if (!empty($fields['PLANNED_DELIVERY_DATE'])) {
            $newDate = DateHelper::toDate((string)$fields['PLANNED_DELIVERY_DATE']);
            if ($newDate !== null) {
                $update['PLANNED_DELIVERY_DATE'] = $newDate;

                $oldDate = $row['PLANNED_DELIVERY_DATE'] ?? null;
                $oldStr  = $oldDate instanceof \Bitrix\Main\Type\Date ? $oldDate->format('Y-m-d') : '';
                if ($oldStr !== '' && $oldStr !== $newDate->format('Y-m-d')) {
                    $update['PLANNED_DELIVERY_CHANGED'] = 'Y';
                }
            }
        }

        if ($update === []) {
            return;
        }

        $update['UPDATED_AT'] = new \Bitrix\Main\Type\DateTime();
        $this->update((int)$row['ID'], $update);
    }
}
