<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;
use Beeralex\Core\Service\UrlService;

/**
 * Цепочка навигации из секций инфоблока
 * @property string $title
 * @property string $to
 */
class BreadcrumbsItemDTO extends Resource
{
    /**
     * @var array{NAME:string, SECTION_PAGE_URL?:string, URL?:string} $sectionItem
     */
    public static function make(array $sectionItem): static
    {
        $urlService = service(UrlService::class);
        $url = $sectionItem['SECTION_PAGE_URL'] ?? $sectionItem['URL'] ?? '';
        if(is_array($url) && isset($url['clean_url'])) {
            $url = $url['clean_url'];
        }elseif (is_string($url)) {
            $url = $urlService->cleanUrl($url);
        } else {
            $url = '';
        }
        return new static([
            'title' => $sectionItem['NAME'] ?? '',
            'to' => $url,
        ]);
    }
}
