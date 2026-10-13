<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

/**
 * Парсинг дат из пейлоадов ApiShip в типы Bitrix ORM.
 */
final class DateHelper
{
    /**
     * ISO8601 (напр. "2023-10-17T12:19:44+03:00") -> Bitrix DateTime. Пусто/мусор -> null.
     */
    public static function toDateTime(?string $value): ?DateTime
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $ts = strtotime($value);

        return $ts !== false ? DateTime::createFromTimestamp($ts) : null;
    }

    /**
     * "YYYY-MM-DD" -> Bitrix Date. Пусто/мусор -> null.
     */
    public static function toDate(?string $value): ?Date
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $ts = strtotime($value);

        return $ts !== false ? Date::createFromTimestamp($ts) : null;
    }
}
