<?php

namespace Beeralex\Api;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;

class UrlHelper
{
    /**
     * Возвращает массив строк для удаления из URL из .settings.php
     */
    public static function getRemoveParts(): array
    {
        $config = Configuration::getInstance()->get('beeralex.api');
        return $config['remove_parts'] ?? Configuration::getInstance('beeralex.api')->get('remove_parts') ?? [];
    }

    /**
     * Удаляет указанные части из URL
     */
    public static function cleanUrl(string $url): string
    {
        $removeParts = static::getRemoveParts();

        foreach ($removeParts as $part) {
            $url = preg_replace('#/' . preg_quote($part, '#') . '(/|$)#', '/', $url);
        }

        $url = preg_replace('#/+#', '/', $url);
        $url = '/' . ltrim($url, '/');
        return rtrim($url, '/') ?: '/';
    }


    /**
     * Возвращает URL раздела инфоблока
     * @param array{
     *     CODE: string,
     *     ID: string
     * } $sectionFields
     * @return array{
     *     url: string,
     *     clean_url: string,
     * }
     */
    public static function getSectionUrl(array $sectionFields, string $template, bool $serverName = false, string $arrType = 'S'): array
    {
        Loader::requireModule('iblock');
        $url = \CIBlock::ReplaceSectionUrl($template, $sectionFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => static::cleanUrl($url)];
    }

    /**
     * Возвращает URL элемента инфоблока
     * @return array{
     *     url: string,
     *     clean_url: string,
     * }
     */
    public static function getDetailUrl(array $elementFields, string $template, bool $serverName = false, string $arrType = 'E'): array
    {
        Loader::requireModule('iblock');
        $url = \CIBlock::ReplaceDetailUrl($template, $elementFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => static::cleanUrl($url)];
    }
}
