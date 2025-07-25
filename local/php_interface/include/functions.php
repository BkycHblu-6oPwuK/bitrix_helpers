<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!function_exists('custom_mail') && Option::get("webprostor.smtp", "USE_MODULE") == "Y") {
    function custom_mail($to, $subject, $message, $additional_headers = '', $additional_parameters = '')
    {
        if (Loader::IncludeModule("webprostor.smtp")) {
            $smtp = new CWebprostorSmtp(false, $additional_headers);
            $result = $smtp->SendMail($to, $subject, $message, $additional_headers, $additional_parameters);
            if ($result)
                return true;
            else
                return false;
        }
    }
}
if (!function_exists('toFile')) {
    function toFile(mixed $data, $path = 'local/logs/1.log'): void
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        (new \Itb\Core\Logger\FileLogger($_SERVER['DOCUMENT_ROOT'] . "/{$path}"))->info('', $data);
    }
}
if (!function_exists('isLighthouse')) {
    function isLighthouse(): bool
    {
        return (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false);
    }
}
if (!function_exists('isLettersUppercase')) {
    /**
     * Проверяет являются ли символы строки заглавными, по умолчанию игнорирует цифры и прочие символы
     */
    function isLettersUppercase(string $str, bool $ignoreNonLetters = true): bool
    {
        if ($ignoreNonLetters) {
            $letters = preg_replace('/[^A-Za-zА-Яа-яЁё]/u', '', $str);
            return $letters !== '' && mb_strtoupper($letters) === $letters;
        } else {
            return preg_match('/^[A-ZА-ЯЁ]+$/u', $str);
        }
    }
}
if (!function_exists('containsOnlyLetters')) {
    /**
     * Проверяет является ли символы в строке только буквами
     */
    function containsOnlyLetters(string $str): bool
    {
        $str = trim($str);
        return $str !== '' && preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $str) === 1;
    }
}
if (!function_exists('isImport')) {
    /**
     * Обмен с 1с или нет
     */
    function isImport(): bool
    {
        return $_REQUEST['mode'] == 'import';
    }
}
