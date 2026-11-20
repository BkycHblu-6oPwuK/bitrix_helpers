<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;
use Bitrix\Sale\EntityMarker;
use function Beeralex\Apiship\log;

/**
 * Помечает заказ Bitrix проблемным, если отправить его в ApiShip не удалось: заказ получает
 * отметку (MARKED / REASON_MARKED) и маркер в блоке «Проблемы» карточки заказа.
 * Так менеджер видит ошибку в списке заказов, а не только в логе.
 *
 * Повторяет то, что делает сам Order::save() при наличии маркеров: saveMarkers() и обновление
 * MARKED/REASON_MARKED напрямую в таблице (без пересохранения заказа и его событий).
 * Снимаются только отметки, поставленные этим классом (причина начинается с REASON_PREFIX).
 */
class OrderErrorMarker
{
    public const REASON_PREFIX = 'ApiShip: ';
    public const ERROR_CODE = 'APISHIP_CREATE_FAILED';

    /** Длина поля REASON_MARKED в b_sale_order. */
    protected const REASON_MAX_LENGTH = 255;

    public function mark(Order $order, string $message): void
    {
        $reason = static::REASON_PREFIX . $message;

        // тот же текст уже записан предыдущей попыткой — не плодим дубли маркеров
        if ($this->isMarkedWith($order, $reason)) {
            return;
        }

        try {
            $this->addMarker($order, $reason);
        } catch (\Throwable $e) {
            log('ApiShip: не удалось добавить маркер заказа: ' . $e->getMessage());
        }

        try {
            OrderTable::update((int)$order->getId(), [
                'MARKED'        => 'Y',
                'REASON_MARKED' => mb_substr($reason, 0, static::REASON_MAX_LENGTH),
            ]);
        } catch (\Throwable $e) {
            log('ApiShip: не удалось пометить заказ: ' . $e->getMessage());
        }
    }

    /**
     * Снимает отметку, если её поставили мы. Маркеры в блоке «Проблемы» закрываются там же
     * менеджером ("Исправлено"): их автоматическое удаление зависит от версии ядра sale.
     */
    public function clear(Order $order): void
    {
        if (!$this->isOurs($order)) {
            return;
        }

        try {
            OrderTable::update((int)$order->getId(), ['MARKED' => 'N', 'REASON_MARKED' => null]);
        } catch (\Throwable $e) {
            log('ApiShip: не удалось снять отметку с заказа: ' . $e->getMessage());
        }
    }

    /** Текст последней ошибки, которую поставили мы (без префикса); иначе null. */
    public function getError(Order $order): ?string
    {
        return $this->isOurs($order)
            ? substr((string)$order->getField('REASON_MARKED'), strlen(static::REASON_PREFIX))
            : null;
    }

    protected function addMarker(Order $order, string $reason): void
    {
        /** @var EntityMarker $markerClass */
        $markerClass = Registry::getInstance($order::getRegistryType())->getEntityMarkerClassName();

        $result = new Result();
        $result->addError(new ResultError($reason, static::ERROR_CODE));

        $markerClass::addMarker($order, $order, $result);
        $markerClass::saveMarkers($order);
    }

    protected function isOurs(Order $order): bool
    {
        return (string)$order->getField('MARKED') === 'Y'
            && str_starts_with((string)$order->getField('REASON_MARKED'), static::REASON_PREFIX);
    }

    protected function isMarkedWith(Order $order, string $reason): bool
    {
        return $this->isOurs($order)
            && (string)$order->getField('REASON_MARKED') === mb_substr($reason, 0, static::REASON_MAX_LENGTH);
    }
}
