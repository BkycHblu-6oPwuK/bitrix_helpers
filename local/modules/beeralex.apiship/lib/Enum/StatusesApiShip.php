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
            self::UPLOADING->value => 'Загрузка информации в систему перевозчика',
            self::UPLOADING_ERROR->value => 'Ошибка передачи информации в систему перевозчика',
            self::UNKNOWN->value => 'Неизвестный статус',
            self::UPLOADED->value => 'Информация успешно загружена в систему перевозчика',
            self::DELIVERY_CANCELED->value => 'Доставка отменена',
            self::DELIVERING->value => 'Передан на доставку в пункте назначения',
            self::DELIVERED->value => 'Доставлен получателю',
            self::RETURNED_FROM_DELIVERY->value => 'Возвращен с доставки',
            self::ON_POINT_IN->value => 'Принят на склад в пункте отправления',
            self::NOT_APPLICABLE->value => 'N/A',
            self::PARTIAL_RETURN->value => 'Частичный возврат',
            self::READY_FOR_RECIPIENT->value => 'Готов к выдаче в пункте назначения',
            self::RETURNED->value => 'Возвращен отправителю',
            self::RETURNING->value => 'Возвращается отправителю',
            self::ON_WAY->value => 'В пути',
            self::LOST->value => 'Утерян',
        ];
    }
}
