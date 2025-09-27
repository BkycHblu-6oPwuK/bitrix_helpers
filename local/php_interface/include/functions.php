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
