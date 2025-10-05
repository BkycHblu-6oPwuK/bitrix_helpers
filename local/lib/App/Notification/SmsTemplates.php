<?php

namespace App\Notification;

use App\Notification\Enum\NotificationStatuses;

class SmsTemplates
{
    protected static function getTemplates(): array
    {
        return [
            NotificationStatuses::NEW_ORDER->value => 'Спасибо за ваш заказ! Оплата прошла успешно. Ваш заказ оформлен и находится в обработке. Номер заказа #ORDER_ID#',
            NotificationStatuses::ORDER_TRANSIT->value => 'Уважаемый/ая #ORDER_USER#. Ваш заказ #ORDER_ID# передан в службу доставки СДЭК. Отследить товар по трек номеру #TRACK_NUMBER#',
            NotificationStatuses::ORDER_READY->value => 'Уважаемый/ая #ORDER_USER#. Ваш заказ #ORDER_ID# готов к выдаче по адресу: Г. Омск, проспект Комарова 2/2, бутик 265 (ТК «Маяк»)',
            NotificationStatuses::SMS_CODE->value => 'Код подтверждения: #CODE#',
        ];
    }

    public static function get(NotificationStatuses $status, array|string $search = '', array|string $replace = '')
    {
        return str_replace($search, $replace, static::getTemplates()[$status->value] ?? '');
    }
}
