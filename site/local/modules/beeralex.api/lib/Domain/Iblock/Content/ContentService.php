<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

class ContentService
{
    public function __construct(
        protected readonly ContentRepository $contentRepository,
        protected readonly ProductRepositoryContract $productsRepository
    ) {}

    public function getContentByCode(string $code): array
    {
        return array_map(function ($item) {
            $type = $item['TYPE']['ITEM']['XML_ID'];
            switch ($type) {
                case ContentTypes::SLIDER->value:
                    $ids = match ($item['PRODUCTS_TYPE']) {
                        ContentTypes::PRODUCTS_NEW->value => $this->getNewProductsIds(),
                        ContentTypes::PRODUCTS_POPULAR->value => $this->getPopularProductsIds(),
                        default => null
                    };

                    return [
                        'TYPE' =>  ContentTypes::SLIDER,
                        'IDS' => $ids ?? array_column($item['PRODUCTS_IDS'], 'VALUE'),
                        'TITLE' => $item['TITLE'] ? $item['TITLE'] : $item['NAME'],
                        'LINK' => $item['LINK']['VALUE'],
                    ];
                case ContentTypes::MAIN_BANNER->value:
                    return [
                        'TYPE' => ContentTypes::MAIN_BANNER,
                        'IDS' => array_column($item['MAIN_BANNER'], 'VALUE'),
                    ];
                case ContentTypes::VIDEO->value:
                    return [
                        'TYPE' =>  ContentTypes::VIDEO,
                        'IDS' => array_column($item['VIDEO_IDS'], 'VALUE'),
                    ];
                case ContentTypes::ARTICLES->value:
                    return [
                        'TYPE' =>  ContentTypes::ARTICLES,
                        'IDS' => array_column($item['ARTICLES_IDS'], 'VALUE'),
                        'TITLE' => $item['ARTICLES_TITLE'] ? $item['ARTICLES_TITLE'] : $item['NAME'],
                        'LINK' => $item['LINK']['VALUE'],
                    ];
                case ContentTypes::FORM->value:
                    return [
                        'TYPE' => ContentTypes::FORM,
                        'ID' => $item['FORM_ID']['VALUE']
                    ];
                case ContentTypes::HTML->value:
                    return [
                        'TYPE' => ContentTypes::HTML,
                        'CONTENT' => $item['HTML']['VALUE'],
                    ];
                default:
                    return [];
            };
        }, $this->contentRepository->getContentByCode($code));
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
