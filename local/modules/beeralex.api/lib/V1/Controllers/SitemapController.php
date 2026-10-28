<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\UrlService;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Main\Data\TaggedCache;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class SitemapController extends Controller
{
    use ApiProcessResultTrait;
    use Cacheable;

    public function configureActions(): array
    {
        return [
            'index' => ['prefilters' => []],
        ];
    }

    public function indexAction(): array
    {
        return $this->process(function () {
            try {
                Loader::includeModule('iblock');

                $iblockService = service(IblockService::class);
                $urlService    = service(UrlService::class);

                $iblockIds = [
                    'articles'         => $iblockService->getIblockIdByCode('articles'),
                    'catalog'          => $iblockService->getIblockIdByCode('catalog'),
                ];

                $cacheSettings = $this->getCacheSettingsDto(
                    3600000,
                    $this->generateCacheId('sitemap_urls', ...array_values($iblockIds)),
                    '/sitemap/',
                    true
                );

                $result = $this->getCached(
                    $cacheSettings,
                    function () use ($urlService, $iblockIds): array {
                        $urls = [];
                        foreach ($iblockIds as $iblockId) {
                            foreach ($this->fetchSectionUrls($iblockId, $urlService) as $url) {
                                $urls[] = $url;
                            }
                            foreach ($this->fetchElementUrls($iblockId, $urlService) as $url) {
                                $urls[] = $url;
                            }
                        }
                        return $urls;
                    },
                    function (array $result, TaggedCache $taggedCache) use ($iblockIds): void {
                        foreach ($iblockIds as $iblockId) {
                            $taggedCache->registerTag('iblock_id_' . $iblockId);
                        }
                    }
                );
                return $result;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function fetchElementUrls(int $iblockId, UrlService $urlService): array
    {
        $repository = new IblockRepository($iblockId);
        $items = $repository->all(
            ['ACTIVE' => 'Y'],
            ['ID', 'CODE', 'IBLOCK_SECTION_ID', 'IBLOCK.DETAIL_PAGE_URL']
        );

        $urls = [];
        foreach ($items as $item) {
            if (empty($item['CODE']) || empty($item['IBLOCK']['DETAIL_PAGE_URL'])) {
                continue;
            }
            $built = $urlService->getDetailUrl(
                ['CODE' => $item['CODE'], 'ID' => $item['ID'], 'IBLOCK_SECTION_ID' => $item['IBLOCK_SECTION_ID']],
                $item['IBLOCK']['DETAIL_PAGE_URL']
            );
            if (!empty($built['clean_url'])) {
                $urls[] = ['loc' => $built['clean_url']];
            }
        }
        return $urls;
    }

    private function fetchSectionUrls(int $iblockId, UrlService $urlService): array
    {
        $repository = new IblockRepository($iblockId);
        $sectionRepository = $repository->getIblockSectionRepository();

        $sections = $sectionRepository->all(
            ['ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y'],
            ['ID', 'CODE', 'IBLOCK.SECTION_PAGE_URL']
        );

        $urls = [];
        foreach ($sections as $section) {
            if (empty($section['CODE']) || empty($section['IBLOCK']['SECTION_PAGE_URL'])) {
                continue;
            }
            $built = $urlService->getSectionUrl(
                ['CODE' => $section['CODE'], 'ID' => $section['ID']],
                $section['IBLOCK']['SECTION_PAGE_URL']
            );
            if (!empty($built['clean_url'])) {
                $urls[] = ['loc' => $built['clean_url']];
            }
        }
        return $urls;
    }
}
