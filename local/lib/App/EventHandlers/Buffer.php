<?php

namespace App\EventHandlers;

class Buffer
{
    public static function onEndBufferContent(&$content): void
    {
        static::deleteCore($content);
        static::deleteAssets($content);
    }

    /**
     * эсперементально удаляем ядро, если не админ и не в админке
     */
    public static function deleteCore(&$content)
    {
        global $USER, $APPLICATION;

        if ((is_object($USER) && $USER->IsAdmin()) || strpos($APPLICATION->GetCurDir(), "/bitrix/") !== false) return;
        if ($APPLICATION->GetProperty("save_kernel") == "Y") return;

        // Удаляем по безопасным паттернам
        $arPatternsToRemove = array(
            '/<script.+?src=".+?js\/main\/core\/.+?(\.min|)\.js\?\d+"><\/script>/',
            '/<script.+?src="\/bitrix\/js\/.+?(\.min|)\.js\?\d+"><\/script>/',
            '/<link.+?href="\/bitrix\/js\/.+?(\.min|)\.css\?\d+".+?>/',
            '/<script.+?src="\/bitrix\/.+?kernel_main.+?(\.min|)\.js\?\d+"><\/script>/',
            '/<link.+?href=".+?kernel_main\/kernel_main(\.min|)\.css\?\d+"[^>]+>/',
            '/<link.+?href=".+?main\/popup(\.min|)\.css\?\d+"[^>]+>/',
            '/<script.+?>BX\.(setCSSList|setJSList)\(\[.+?\]\).*?<\/script>/',
            '/<script.+?>if\(\!window\.BX\)window\.BX.+?<\/script>/',
            '/<script[^>]+?>\(window\.BX\|\|top\.BX\)\.message[^<]+<\/script>/',
            '/<script[^>]+?>.+?bx-core.*?<\/script>/',
            '/<link.+?href=".+?kernel_main\/kernel_main_v1\.css\?\d+"[^>]+>/',
            '/<link.+?href=".+?bitrix\/js\/main\/core\/css\/core[^"]+"[^>]+>/',
            '/<link.+?href=".+?bitrix\/templates\/[\w\d_-]+\/styles.css[^"]+"[^>]+>/',
            '/<link.+?href=".+?bitrix\/templates\/[\w\d_-]+\/template_styles.css[^"]+"[^>]+>/',
        );

        $content = preg_replace($arPatternsToRemove, "", $content);

        // безопасно удаяем скрипты с BX
        $content = preg_replace_callback(
            '/<script\b[^>]*>[\s\S]*?<\/script>/i',
            function ($matches) {
                $script = $matches[0];
                if (preg_match('/\b(BX\.|window\.BX)\b/', $script)) {
                    return '';
                }
                return $script;
            },
            $content
        );

        $content = preg_replace("/\n{2,}/", "\n\n", $content);
    }


    /**
     * сносим общие ассеты ненужные для пользователей в публичной части
     */
    public static function deleteAssets(&$content)
    {
        global $USER, $APPLICATION;
        if ((is_object($USER) && $USER->IsAdmin()) || strpos($APPLICATION->GetCurDir(), "/bitrix/") !== false) return;

        $arPatternsToRemove = array(
            '/<link.+?href=".+?bitrix\/.+?\/bootstrap\.css[^"]*"[^>]*>/',
            '/<link.+?href=".+?bitrix\/.+?\/bootstrap\.min.css[^"]*"[^>]*>/',
            '/<script.+?src=".+?bitrix\/.+?\/bootstrap\.js[^"]*"[^>]*><\/script>/',
            '/<script.+?src=".+?bitrix\/.+?\/bootstrap\.min.js[^"]*"[^>]*><\/script>/',
        );

        $content = preg_replace($arPatternsToRemove, "", $content);
        $content = preg_replace("/\n{2,}/", "\n\n", $content);
    }
}
