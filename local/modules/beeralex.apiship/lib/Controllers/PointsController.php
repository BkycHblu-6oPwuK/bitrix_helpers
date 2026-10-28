<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Beeralex\Apiship\Contracts\PointServiceContract;

/**
 * Ajax-контроллер для работы с ПВЗ.
 * URL: /bitrix/services/main/ajax.php?mode=class&c=beeralex:apiship.points&action=list
 *
 * Используется Nuxt-фронтом для отображения карты ПВЗ и выбора пункта выдачи.
 * Выбор ПВЗ записывается через action=select (или отдельным запросом).
 */
class PointsController extends Controller
{
    public function configureActions(): array
    {
        return [
            'list'   => ['prefilters' => []],
            'select' => [],
        ];
    }

    /**
     * Список ПВЗ с пагинацией.
     * Параметры (GET/POST): filter[city], filter[providerKey], filter[type], filter[cod] и др.;
     *                       limit (default 50), offset (default 0).
     *
     * @return array{items: array[], total: int}|null
     */
    public function listAction(): ?array
    {
        $request = $this->getRequest();
        $filter  = (array)($request->get('filter') ?? []);
        $limit   = max(1, min(100, (int)($request->get('limit') ?? 50)));
        $offset  = max(0, (int)($request->get('offset') ?? 0));

        try {
            $result = \service(PointServiceContract::class)->getPoints($filter, $limit, $offset);
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return null;
        }

        // PointDto наследует Dictionary с toArray() — безопасная сериализация
        $items = array_map(fn($dto) => $dto->toArray(), $result['items']);

        return ['items' => $items, 'total' => $result['total']];
    }

    /**
     * Записывает выбранный ПВЗ в свойство заказа.
     * Параметры (POST): orderId (int), pointId (string).
     */
    public function selectAction(): ?array
    {
        $request  = $this->getRequest();
        $orderId  = (int)$request->getPost('orderId');
        $pointId  = (string)$request->getPost('pointId');

        if ($orderId <= 0 || $pointId === '') {
            $this->addError(new Error('Необходимо передать orderId и pointId'));
            return null;
        }

        try {
            $ok = \service(PointServiceContract::class)->setPickupPoint($orderId, $pointId);
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage()));
            return null;
        }

        if (!$ok) {
            $this->addError(new Error('Не удалось сохранить ПВЗ для заказа #' . $orderId));
            return null;
        }

        return ['saved' => true];
    }
}
