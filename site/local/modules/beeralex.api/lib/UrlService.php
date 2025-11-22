<?php
declare(strict_types=1);

namespace Beeralex\Api;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;

class UrlService
{
    protected readonly array $removeParts;
    
    public function __construct()
    {
        Loader::requireModule('iblock');
        $this->removeParts = static::getRemoveParts();
    }

    public static function getRemoveParts(): array
    {
        $config = Configuration::getInstance()->get('beeralex.api');
        return $config['remove_parts'] ?? Configuration::getInstance('beeralex.api')->get('remove_parts') ?? [];
    }

    /**
     * Удаляет указанные части из URL
     */
    public function cleanUrl(string $url): string
    {
        foreach ($this->removeParts as $part) {
            $url = preg_replace('#(^|/)' . preg_quote($part, '#') . '(/|$)#', '/', $url);
        }

        $url = preg_replace('#/+#', '/', $url);
        $url = '/' . trim($url, '/');

        return $url === '/' ? '/' : rtrim($url, '/');
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
    public function getSectionUrl(array $sectionFields, string $template, bool $serverName = false, string $arrType = 'S'): array
    {
        $url = \CIBlock::ReplaceSectionUrl($template, $sectionFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => $this->cleanUrl($url)];
    }

    /**
     * Возвращает URL элемента инфоблока
     * @return array{
     *     url: string,
     *     clean_url: string,
     * }
     */
    public function getDetailUrl(array $elementFields, string $template, bool $serverName = false, string $arrType = 'E'): array
    {
        $url = \CIBlock::ReplaceDetailUrl($template, $elementFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => $this->cleanUrl($url)];
    }
}
