<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Enum;

enum StatusesApiShip: string
{
    case UPLOADING = 'uploading';
    case UPLOADING_ERROR = 'uploadingError';
    case UNKNOWN = 'unknown';
    case UPLOADED = 'uploaded';
    case DELIVERY_CANCELED = 'deliveryCanceled';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case RETURNED_FROM_DELIVERY = 'returnedFromDelivery';
    case ON_POINT_IN = 'onPointIn';
    case NOT_APPLICABLE = 'notApplicable';
    case PARTIAL_RETURN = 'partialReturn';
    case READY_FOR_RECIPIENT = 'readyForRecipient';
    case RETURNED = 'returned';
    case RETURNING = 'returning';
    case ON_WAY = 'onWay';
    case LOST = 'lost';

    /** 
     * получить карту где ключ это значение case enum а значение это текст статуса
     */
    public static function getMap(): array
    {
        return [
            static::UPLOADING->value => 'Загрузка информации в систему перевозчика',
            static::UPLOADING_ERROR->value => 'Ошибка передачи информации в систему перевозчика',
            static::UNKNOWN->value => 'Неизвестный статус',
            static::UPLOADED->value => 'Информация успешно загружена в систему перевозчика',
            static::DELIVERY_CANCELED->value => 'Доставка отменена',
            static::DELIVERING->value => 'Передан на доставку в пункте назначения',
            static::DELIVERED->value => 'Доставлен получателю',
            static::RETURNED_FROM_DELIVERY->value => 'Возвращен с доставки',
            static::ON_POINT_IN->value => 'Принят на склад в пункте отправления',
            static::NOT_APPLICABLE->value => 'N/A',
            static::PARTIAL_RETURN->value => 'Частичный возврат',
            static::READY_FOR_RECIPIENT->value => 'Готов к выдаче в пункте назначения',
            static::RETURNED->value => 'Возвращен отправителю',
            static::RETURNING->value => 'Возвращается отправителю',
            static::ON_WAY->value => 'В пути',
            static::LOST->value => 'Утерян',
        ];
    }
}
