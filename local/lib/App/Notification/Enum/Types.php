<?php

namespace App\Notification\Enum;

enum Types: string
{
    case ORDER_INFO = 'order_info';
    case PROMOTIONS = 'promotions';
    case RECOMENDATIONS = 'recommendations';
    
    /**
     * @return static[]
     */
    public static function getAll(): array
    {
        return [
            static::ORDER_INFO,
            static::PROMOTIONS,
            static::RECOMENDATIONS,
        ];
    }

    public static function get(string $code): ?static
    {
        foreach (static::getAll() as $type) {
            if ($type->value === $code) {
                return $type;
            }
        }
        return null;
    }
}
