<?php

namespace Itb\User\Phone;

class PhoneFormatter
{
    /**
     * Преобразует телефон в вид для хранения в БД. В БД храним только строку с цифрами без какого-лиюо форматирования.
     * Метод работает для любых переданных параметров: для вида хранящимся в БД, для пользовательского инпута, для null
     *
     * @param null|string $phone
     *
     * @return null|string
     */
    public function formatForDb(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Преобразует телефон в формат +7 995 087 69 04
     *
     * @param null|string $phone
     *
     * @return null|string
     */
    public function formatForSite(?string $phone): ?string
    {
        $digits = $this->formatForDb($phone);

        if (!$digits || strlen($digits) < 11) {
            return null;
        }

        return "+7 " . substr($digits, 1, 3) . " " . substr($digits, 4, 3) . " " . substr($digits, 7, 2) . " " . substr($digits, 9, 2);
    }
}
