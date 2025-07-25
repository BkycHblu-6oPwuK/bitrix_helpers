<?php

namespace Itb\Notification\Enum;

enum Channels: string
{
    case EMAIL = 'email';
    case SMS = 'sms';

    /**
     * @return static[]
     */
    public static function getAll(): array
    {
        return [
            static::EMAIL,
            static::SMS,
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
