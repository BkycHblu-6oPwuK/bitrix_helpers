<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;
use Beeralex\Core\Service\UrlService;

/** 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $url
 * @property string $pictureSrc
 * DTO для секции инфоблока, например элемент из catalog.section.list
 */
class SectionDTO extends Resource
{
    public static function make(array $sectionItem): static
    {
        $urlService = service(UrlService::class);
        $url = $sectionItem['SECTION_PAGE_URL'] ?? $sectionItem['URL'] ?? '';
        if ($url) {
            $url = $urlService->cleanUrl($url);
        }
        return new static([
            'id' => (int)($sectionItem['ID'] ?? 0),
            'name' => $sectionItem['NAME'] ?? '',
            'code' => $sectionItem['CODE'] ?? '',
            'url' => $url,
            'pictureSrc' => $sectionItem['PICTURE_SRC'] ?? '',
        ]);
    }
}
