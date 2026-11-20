<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Controllers;

use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Support\OrderAccess;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

/**
 * Ajax-действия над заказом из админки.
 * Вызов: BX.ajax.runAction('beeralex:apiship.order.create', {data: {orderId: 123}}).
 */
class OrderController extends Controller
{
    public function configureActions(): array
    {
        return [
            'create' => [
                'prefilters' => [
                    new Authentication(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Csrf(),
                ],
            ],
        ];
    }

    /**
     * Создать заказ в ApiShip для заказа Bitrix. Идемпотентно: если заказ уже создан, вернёт его ID.
     *
     * @return array{apiShipOrderId:int}|null
     */
    public function createAction(int $orderId): ?array
    {
        if (!OrderAccess::canCreateOrder()) {
            $this->addError(new Error('Недостаточно прав для создания заказа в ApiShip', 'ACCESS_DENIED'));
            return null;
        }

        try {
            $result = \service(OrderServiceContract::class)->createForBitrixOrder($orderId);
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage(), 'APISHIP_CREATE_FAILED'));
            return null;
        }

        return ['apiShipOrderId' => (int)$result->orderId];
    }
}
