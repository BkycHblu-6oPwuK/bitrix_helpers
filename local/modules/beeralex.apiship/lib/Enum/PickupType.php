<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Enum;

enum PickupType: int
{
    case FROM_DOOR = 1;
    case SELF_DELIVERY = 2;

    public function label(): string
    {
        return match ($this) {
            self::FROM_DOOR     => 'Забор от двери',
            self::SELF_DELIVERY => 'Самозавоз (привоз на склад СД)',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[(string)$case->value] = $case->label();
        }
        return $result;
    }
}
