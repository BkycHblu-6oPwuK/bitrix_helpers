<?php

namespace App\User\Enum;

enum Gender : string
{
    case MAN = 'man';
    case WOMAN = 'woman';

    case PROFILE_MAN = 'M';
    case PROFILE_WOMAN = 'F';

    public static function getProfileGenderBySite(string $gender) : ?string
    {
        $gender = match ($gender) {
            self::MAN->value => self::PROFILE_MAN->value,
            self::WOMAN->value => self::PROFILE_WOMAN->value,
            default => null
        };
        return $gender;
    }
}