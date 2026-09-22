<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery\Tracking;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Tracking\Base;
use Bitrix\Sale\Delivery\Tracking\StatusResult;
use Bitrix\Sale\Delivery\Tracking\Statuses;
use Beeralex\Apiship\Contracts\StatusServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;

Loc::loadMessages(__FILE__);

/**
 * Трекинг заказа ApiShip для покупателя (история статусов).
 *
 * $trackingNumber здесь — ID заказа в ApiShip (apiShipOrderId), а не физический трек-номер ТК,
 * т.к. единая точка получения статуса — сам ApiShip (агрегатор уже транслирует статус СД).
 */
class ApiShipTracking extends Base
{
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
        } catch (ApiShipException $e) {
            $result->addError(new Error($e->getMessage()));
            return $result;
        }

        $result->status = Statuses::UNKNOWN;
        $result->description = $status->getName() ?? $status->getKey();
        $result->trackingNumber = (string)$apiShipOrderId;
        $result->lastChangeTimestamp = $status->getDate() ? strtotime($status->getDate()) : time();

        return $result;
    }
}
