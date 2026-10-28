<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery\Tracking;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Tracking\Base;
use Bitrix\Sale\Delivery\Tracking\StatusResult;
use Bitrix\Sale\Delivery\Tracking\Statuses;
use Beeralex\Apiship\Contracts\StatusServiceContract;

Loc::loadMessages(__FILE__);

class ApiShipTracking extends Base
{
    /** Маппинг ключей статусов ApiShip -> константы Bitrix Statuses */
    private const STATUS_MAP = [
        'delivered'            => Statuses::HANDED,
        'delivering'           => Statuses::ON_THE_WAY,
        'onWay'                => Statuses::ON_THE_WAY,
        'readyForRecipient'    => Statuses::ARRIVED,
        'uploaded'             => Statuses::WAITING_SHIPMENT,
        'onPointIn'            => Statuses::WAITING_SHIPMENT,
        'uploading'            => Statuses::WAITING_SHIPMENT,
        'returned'             => Statuses::RETURNED,
        'returning'            => Statuses::RETURNED,
        'returnedFromDelivery' => Statuses::RETURNED,
        'partialReturn'        => Statuses::RETURNED,
        'lost'                 => Statuses::PROBLEM,
        'deliveryCanceled'     => Statuses::PROBLEM,
        'uploadingError'       => Statuses::PROBLEM,
        'unknown'              => Statuses::UNKNOWN,
        'notApplicable'        => Statuses::UNKNOWN,
    ];

    public function getClassTitle(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_TRACKING_TITLE') ?: 'ApiShip';
    }

    public function getClassDescription(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_TRACKING_DESCRIPTION') ?: 'Трекинг через ApiShip';
    }

    public function getParamsStructure(): array
    {
        return [];
    }

    public function getStatus($trackingNumber): StatusResult
    {
        $result = new StatusResult();
        $apiShipOrderId = (int)$trackingNumber;

        if ($apiShipOrderId <= 0) {
            $result->addError(new Error('Некорректный ID заказа ApiShip'));
            return $result;
        }

        try {
            $status = \service(StatusServiceContract::class)->getStatus($apiShipOrderId);
        } catch (\Throwable $e) {
            $result->addError(new Error($e->getMessage()));
            return $result;
        }

        $statusKey = $status->key ?? '';
        $result->status = static::STATUS_MAP[$statusKey] ?? Statuses::UNKNOWN;
        $result->description = $status->name ?? $statusKey;
        $result->trackingNumber = (string)$apiShipOrderId;

        $ts = $status->date ? strtotime((string)$status->date) : false;
        $result->lastChangeTimestamp = $ts !== false ? $ts : time();

        return $result;
    }
}
