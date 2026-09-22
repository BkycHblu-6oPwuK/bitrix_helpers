<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Exception;

/**
 * Ошибка валидации данных перед отправкой запроса в ApiShip
 * (например, отсутствует обязательное поле заказа/расчёта).
 */
class ApiShipValidationException extends ApiShipException
{
}
