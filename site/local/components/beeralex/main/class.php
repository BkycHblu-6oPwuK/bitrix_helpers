<?php

use App\Repository\ProductsRepository;
use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Content\MainRepository;
use Beeralex\Catalog\Enum\DIServiceKey;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexMain extends \CBitrixComponent
{
    protected ProductsRepository $productsRepository;
    protected MainRepository $mainRepository;

    public function onPrepareComponentParams($params)
    {
        Loader::requireModule('beeralex.catalog');
        $this->productsRepository = service(service(DIServiceKey::PRODUCT_REPOSITORY->value));
        $this->mainRepository = service(MainRepository::class);
        return $params;
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult = $this->getContent();
            $this->endResultCache();
        }
        $this->includeComponentTemplate();
    }

    public function getContent(): array
    {
        return array_map(function ($item) {
            $type = $item['TYPE']['ITEM']['XML_ID'];
            switch ($type) {
                case MainContentTypes::SLIDER->value:
                    $ids = match ($item['PRODUCTS_TYPE']) {
                        MainContentTypes::SLIDER_NEW->value => $this->getNewProductsIds(),
                        MainContentTypes::PRODUCTS_POPULAR->value => $this->getPopularProductsIds(),
                        default => null
                    };
                    if ($ids === null && !empty($item['PRODUCTS_SECTION_IDS'])) {
                        $productsBySections = $this->productsRepository->getProductsIdsBySections([$item['PRODUCTS_SECTION_IDS']['VALUE']]);
                        foreach ($productsBySections as $product) {
                            $ids[] = (int)$product['ID'];
                        }
                    }
                    return [
                        'TYPE' =>  MainContentTypes::SLIDER,
                        'IDS' => $ids ?? array_column($item['PRODUCTS_IDS'], 'VALUE'),
                        'TITLE' => $item['PRODUCTS_TITLE'] ? $item['PRODUCTS_TITLE']['VALUE'] : $item['NAME'],
                        'TEXT' => $item['PRODUCTS_TEXT']['VALUE'] ?? null,
                        'IMAGE_SRC' => $item['PRODUCTS_IMAGE']['VALUE'] ?? null,
                        'LINK' => $item['LINK']['VALUE'],
                    ];
                case MainContentTypes::MAIN_BANNER->value:
                    return [
                        'TYPE' => MainContentTypes::MAIN_BANNER,
                    ];
                case MainContentTypes::VIDEO->value:
                    return [
                        'TYPE' =>  MainContentTypes::VIDEO,
                        'IDS' => array_column($item['VIDEO_IDS'], 'VALUE'),
                    ];
                case MainContentTypes::ARTICLES->value:
                    $typeSlider = $item['ARTICLES_TYPE']['ITEM']['XML_ID'] ?? '';
                    return [
                        'TYPE' =>  MainContentTypes::ARTICLES,
                        'TYPE_SLIDER' => MainContentTypes::tryFrom($typeSlider),
                        'IDS' => array_column($item['ARTICLES_IDS'], 'VALUE'),
                        'TITLE' => $item['ARTICLES_TITLE'] ? $item['ARTICLES_TITLE'] : $item['NAME'],
                        'LINK' => $item['LINK']['VALUE'],
                    ];
                default:
                    return [];
            };
        }, $this->mainRepository->getContent());
    }

    protected function getNewProductsIds(): array
    {
        return $this->productsRepository->getNewProductsIds();
    }

    protected function getPopularProductsIds(): array
    {
        return $this->productsRepository->getPopularProductsIds();
    }
}
