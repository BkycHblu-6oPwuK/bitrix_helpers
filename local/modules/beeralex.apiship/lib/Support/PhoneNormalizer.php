<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

/**
 * Приводит телефон к виду, который принимает ApiShip (phone: до 15 символов).
 * "+7 (999) 123-45-67" -> "+79991234567", "8 999 123 45 67" -> "+79991234567".
 */
class PhoneNormalizer
{
    protected const MAX_LENGTH = 15;

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }

        return substr('+' . $digits, 0, static::MAX_LENGTH);
    }
}
