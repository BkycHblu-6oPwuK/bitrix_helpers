<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Main\Engine\CurrentUser;

/**
 * Права на действия модуля в карточке заказа админки.
 */
class OrderAccess
{
    /** Минимальный уровень доступа к модулю «Магазин» для создания заказа в ApiShip (U — работа с заказами). */
    protected const MIN_SALE_RIGHT = 'U';

    public static function canCreateOrder(): bool
    {
        $user = CurrentUser::get();
        if ($user->isAdmin()) {
            return true;
        }

        return (string)$GLOBALS['APPLICATION']->GetGroupRight('sale') >= static::MIN_SALE_RIGHT;
    }
}
