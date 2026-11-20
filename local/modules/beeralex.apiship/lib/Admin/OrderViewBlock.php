<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Admin;

use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Support\OrderAccess;
use Beeralex\Apiship\Support\OrderErrorMarker;
use Beeralex\Apiship\Support\OrderInfoReader;
use Beeralex\Apiship\Support\ShipmentLocator;
use Bitrix\Sale\Order;
use function Beeralex\Apiship\log;

/**
 * Блок «Доставка ApiShip» на вкладке «Заказ» страницы просмотра заказа в админке
 * (событие main:OnAdminSaleOrderViewDraggable). Показывает связь с заказом ApiShip, историю статусов,
 * журнал вебхуков и кнопку «Создать заказ в ApiShip», если заказ ещё не создан.
 */
class OrderViewBlock
{
    public const BLOCK_CODE = 'beeralex_apiship_order';
    public const BLOCK_SET = 'BeeralexApiship';

    /** Обработчик события: описывает набор блоков и их колбэки. */
    public static function onInit(): array
    {
        return [
            'BLOCKSET'        => static::BLOCK_SET,
            'getScripts'      => [static::class, 'getScripts'],
            'getBlocksBrief'  => [static::class, 'getBlocksBrief'],
            'getBlockContent' => [static::class, 'getBlockContent'],
        ];
    }

    /** @param array<string,mixed> $args */
    public static function getBlocksBrief(array $args): array
    {
        return static::order($args) !== null ? [static::BLOCK_CODE => ['TITLE' => 'Доставка ApiShip']] : [];
    }

    /** @param array<string,mixed> $args */
    public static function getScripts(array $args): string
    {
        return static::order($args) !== null ? (new OrderInfoRenderer())->scripts() : '';
    }

    /** @param array<string,mixed> $args */
    public static function getBlockContent(string $blockCode, string $selectedTab, array $args): string
    {
        if ($blockCode !== static::BLOCK_CODE || $selectedTab !== 'tab_order') {
            return '';
        }

        $order = static::order($args);
        if ($order === null) {
            return '';
        }

        try {
            $orderId = (int)$order->getId();
            $info = \service(OrderInfoReader::class)->read($orderId);
            $marker = \service(OrderErrorMarker::class);

            return (new OrderInfoRenderer())->render(
                $info,
                $orderId,
                $marker->getError($order),
                $info['order'] === null && OrderAccess::canCreateOrder()
            );
        } catch (\Throwable $e) {
            log('ApiShip: не удалось показать блок заказа: ' . $e->getMessage());

            return '<p style="color:#c00">Не удалось загрузить данные ApiShip: ' . htmlspecialcharsbx($e->getMessage()) . '</p>';
        }
    }

    /**
     * Заказ, для которого показываем блок: сохранённый, с доставкой ApiShip или уже созданным заказом ApiShip
     * (доставку могли поменять после создания). Иначе null — блока нет.
     *
     * @param array<string,mixed> $args
     */
    protected static function order(array $args): ?Order
    {
        $order = $args['ORDER'] ?? null;
        if (!$order instanceof Order || (int)$order->getId() <= 0) {
            return null;
        }

        if (ShipmentLocator::findApiShipShipment($order) !== null) {
            return $order;
        }

        return \service(OrderRepository::class)->getApiShipOrderId((int)$order->getId()) !== null ? $order : null;
    }
}
