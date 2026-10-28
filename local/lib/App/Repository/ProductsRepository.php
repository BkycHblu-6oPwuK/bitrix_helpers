<?php

declare(strict_types=1);

namespace App\Repository;

use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\ProductsRepository as CatalogProductsRepository;
use Beeralex\Core\Service\CatalogService;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\UrlService;

class ProductsRepository extends CatalogProductsRepository
{
    public function __construct(
        string $iblockCode,
        CatalogService $catalogService,
        CatalogViewedProductRepository $catalogViewedProductRepository,
        UrlService $urlService,
        protected readonly FileService $fileService,
    ) {
        parent::__construct($iblockCode, $catalogService, $catalogViewedProductRepository, $urlService);
    }

    protected function afterFindProducts(array &$products, array $productIds): void
    {
        $pictureIds = [];

        foreach ($products as $item) {
            if ($item['PREVIEW_PICTURE']) {
                $pictureIds[] = (int)$item['PREVIEW_PICTURE'];
            }
            if ($item['DETAIL_PICTURE']) {
                $pictureIds[] = (int)$item['DETAIL_PICTURE'];
            }
            if (!empty($item['MORE_PHOTO']) && is_array($item['MORE_PHOTO'])) {
                foreach ($item['MORE_PHOTO'] as $photo) {
                    $pictureIds[] = (int)$photo['VALUE'];
                }
            }
        }

        $paths = $this->fileService->getPathByIds($pictureIds);
        foreach ($products as &$product) {
            if ($product['PREVIEW_PICTURE'] && isset($paths[(int)$product['PREVIEW_PICTURE']])) {
                $product['PREVIEW_PICTURE_SRC'] = $paths[(int)$product['PREVIEW_PICTURE']];
            }
            if ($product['DETAIL_PICTURE'] && isset($paths[(int)$product['DETAIL_PICTURE']])) {
                $product['DETAIL_PICTURE_SRC'] = $paths[(int)$product['DETAIL_PICTURE']];
            }
            if (!empty($product['MORE_PHOTO']) && is_array($product['MORE_PHOTO'])) {
                foreach ($product['MORE_PHOTO'] as &$photo) {
                    $photoId = (int)$photo['VALUE'];
                    if (isset($paths[$photoId])) {
                        $photo['VALUE'] = $paths[$photoId];
                    }
                }
            }
        }
        unset($product);
    }

    public function getProductsIdsBySections(array $sectionIds, bool $onlyActive = true): array
    {
        if (empty($sectionIds)) {
            return [];
        }

        $filter = ['=IBLOCK_SECTION_ID' => $sectionIds];
        if ($onlyActive) {
            $filter['=ACTIVE'] = 'Y';
            $filter['CATALOG.AVAILABLE'] = 'Y';
        }

        return $this->findAll(
            $filter,
            ['ID', 'IBLOCK_SECTION_ID'],
        );
    }
}
