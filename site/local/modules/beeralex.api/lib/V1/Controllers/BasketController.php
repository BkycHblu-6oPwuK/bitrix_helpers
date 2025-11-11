<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Bitrix\Main\Engine\Controller;

/**
 * контроллер корзины, пусть использует basketFacade для действий над добавлением, удалением, получением и т.д.
 */
class BasketController extends Controller
{
    public function configureActions()
    {
        return [
            'get' => [
                'prefilters' => [],
            ],
        ];
    }

    public function getAction()
    {
        // получение корзины
    }
}
