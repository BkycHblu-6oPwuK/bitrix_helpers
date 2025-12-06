<?php

declare(strict_types=1);

namespace App\Repository;

use Beeralex\Catalog\Repository\ProductsRepository as CatalogProductsRepository;
use Beeralex\Core\Service\FileService;

class ProductsRepository extends CatalogProductsRepository
{
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        if (empty($productIds)) {
            return [];
        }

        $filter = ['ID' => $productIds];
        if ($onlyActive) {
            $filter['ACTIVE'] = 'Y';
        }

        $items = $this->findAll(
            $filter,
            ['*', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CATALOG', 'BRAND_REF', 'MORE_PHOTO'],
        );

        $products = [];
        $pictureIds = [];
        $fileService = service(FileService::class);

        foreach ($items as $item) {
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
            $products[(int)$item['ID']] = $item;
        }

        $paths = $fileService->getPathByIds($pictureIds);
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
                        $photo['PICTURE_SRC'] = $paths[$photoId];
                    }
                }
            }
        }

        $result = [];
        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $result[] = $products[$id];
            }
        }

        return $result;
    }
}
