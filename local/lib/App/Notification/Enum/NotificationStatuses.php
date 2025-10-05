<?php

namespace App\Notification\Enum;

enum NotificationStatuses: string
{
    case NEW_ORDER = "SALE_NEW_ORDER";
    case ORDER_TRANSIT = "SALE_STATUS_CHANGED_D";
    case ORDER_READY = "SALE_STATUS_CHANGED_R";
    case SMS_CODE = 'SMS_CODE';

    /**
     * @return static[]
     */
    public static function getAll(): array
    {
        return array_merge(static::getOrderStatuses(), static::getAboutStatuses());
    }

    public static function get(string $code, ?Types $type = null): ?static
    {
        if ($type === null) {
            $type = static::resolveTypeByCode($code);
        }
        $statuses = match ($type) {
            Types::ORDER_INFO => static::getOrderStatuses(),
            default => static::getAll(),
        };
        foreach ($statuses as $status) {
            if ($status->value === $code) {
                return $status;
            }
        }
        return null;
    }

    public static function resolveTypeByCode(string $code): ?Types
    {
        return match (true) {
            in_array($code, array_map(fn($e) => $e->value, static::getOrderStatuses())) => Types::ORDER_INFO,
            // другие правила
            default => null,
        };
    }

    protected static function getOrderStatuses(): array
    {
        return [
            static::NEW_ORDER,
            static::ORDER_TRANSIT,
            static::ORDER_READY,
        ];
    }

    protected static function getAboutStatuses() :array
    {
        return [
            static::SMS_CODE,
        ];
    }
}
