<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Enum;

enum DeliveryType: int
{
    case TO_DOOR = 1;
    case TO_POINT = 2;

    public function label(): string
    {
        return match ($this) {
            self::TO_DOOR  => 'До двери',
            self::TO_POINT => 'До ПВЗ',
        };
    }

    /** @return array<string,string> value => label, для ENUM в админке */
    public static function options(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[(string)$case->value] = $case->label();
        }
        return $result;
    }
}
