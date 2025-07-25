<?php

use Illuminate\Support\Collection;
use Itb\Catalog\Products;
use Itb\Core\Helpers\IblockHelper;
use Itb\Enum\ContentTypes;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ItbContent extends \CBitrixComponent
{
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult = $this->getContent();
            $this->endResultCache();
        }
        $this->includeComponentTemplate();
    }

    protected function getContent(): array
    {
        return collect(IblockHelper::getElementApiTableByCode('content')::query()->where('ACTIVE', 'Y')->setSelect(
            [
                'ID',
                'NAME',
                'TITLE' => 'PRODUCTS_TITLE.VALUE',
                'PRODUCTS_IDS_VALUE' => 'PRODUCTS_IDS.VALUE',
                'PRODUCTS_TYPE_VALUE' => 'PRODUCTS_TYPE.ITEM.XML_ID',
                'BIG' => 'PRODUCT_BIG.VALUE',
                'TYPE_VALUE' => 'TYPE.ITEM.XML_ID',
                'LINK_VALUE' => 'LINK.VALUE',
                'VIDEO_PREVIEW_ID' => 'PREVIEW_VIDEO.VALUE',
                'VIDEO_TITLE_VALUE' => 'VIDEO_TITLE.VALUE',
                'VIDEO_TEXT_VALUE' => 'VIDEO_TEXT.VALUE',
                'VIDEO_LINK_VALUE' => 'VIDEO_LINK.VALUE',
                'TWO_ARTICLES_IDS_VALUE' => 'TWO_ARTICLES_IDS.VALUE',
                'ARTICLES_IDS_VALUE' => 'ARTICLES_IDS.VALUE',
                'ARTICLES_TITLE_VALUE' => 'ARTICLES_TITLE.VALUE',
                'CATALOG_RAZDEL_IDS_VALUE' => 'CATALOG_RAZDEL_IDS.VALUE'
            ]
        )
            ->setOrder(['SORT' => 'ASC'])
            ->fetchAll())->groupBy('ID')
            ->map(function (Collection $item) {
                $firstItem = $item->first();
                $type = $firstItem['TYPE_VALUE'];
                switch ($type) {
                    case ContentTypes::SLIDER->value:
                        $ids = match ($firstItem['PRODUCTS_TYPE_VALUE']) {
                            ContentTypes::PRODUCTS_NEW->value => $this->getNewProductsIds(),
                            ContentTypes::PRODUCTS_POPULAR->value => $this->getPopularProductsIds(),
                            default => null
                        };
                        $getIdsFromProperty = static function () use (&$item) {
                            return $item->pluck(['PRODUCTS_IDS_VALUE'])->filter(fn($id) => isset($id))->unique()->map(fn($item) => (int)$item)->toArray();
                        };
                        if($ids){
                            if($ids->count() < Products::SLIDER_COUNT){
                                $propertyIds = $getIdsFromProperty();
                                if(!empty($propertyIds)) {
                                    $ids = $ids->merge($propertyIds)->slice(0, Products::SLIDER_COUNT);
                                    $firstItem['LINK_VALUE'] = '';
                                }
                            }
                            $ids = $ids->toArray();
                        }
                        return [
                            'type' =>  ContentTypes::SLIDER,
                            'ids' => $ids ?? $getIdsFromProperty(),
                            'title' => $firstItem['TITLE'] ? $firstItem['TITLE'] : $firstItem['NAME'],
                            'bigId' => $firstItem['BIG'] ? (int)$firstItem['BIG'] : 0,
                            'link' => $firstItem['LINK_VALUE'],
                        ];
                    case ContentTypes::VIDEO->value:
                        return [
                            'type' =>  ContentTypes::VIDEO,
                            'video_preview_id' => $firstItem['VIDEO_PREVIEW_ID'],
                            'title' => $firstItem['VIDEO_TITLE_VALUE'] ? $firstItem['VIDEO_TITLE_VALUE'] : $firstItem['NAME'],
                            'text' => $firstItem['VIDEO_TEXT_VALUE'] ? unserialize($firstItem['VIDEO_TEXT_VALUE'])['TEXT'] : '',
                            'video_link' => $firstItem['VIDEO_LINK_VALUE'],
                        ];
                    case ContentTypes::TWO_ARTICLES->value:
                        return [
                            'type' =>  ContentTypes::TWO_ARTICLES,
                            'ids' => $item->pluck(['TWO_ARTICLES_IDS_VALUE'])->splice(0, 2)->toArray(),
                        ];
                    case ContentTypes::VKONTAKTE->value:
                        return [
                            'type' =>  ContentTypes::VKONTAKTE,
                            'title' => $firstItem['NAME'],
                            'link' => $firstItem['LINK_VALUE'],
                        ];
                    case ContentTypes::ARTICLES->value:
                        return [
                            'type' =>  ContentTypes::ARTICLES,
                            'ids' => $item->pluck(['ARTICLES_IDS_VALUE'])->toArray(),
                            'title' => $firstItem['ARTICLES_TITLE_VALUE'] ? $firstItem['ARTICLES_TITLE_VALUE'] : $firstItem['NAME'],
                            'link' => $firstItem['LINK_VALUE'],
                        ];
                    case ContentTypes::CATALOG_RAZDEL->value:
                        return [
                            'type' =>  ContentTypes::CATALOG_RAZDEL,
                            'ids' => $item->pluck(['CATALOG_RAZDEL_IDS_VALUE'])->toArray(),
                        ];
                    default:
                        return [];
                }
            })
            ->toArray();
    }

    protected function getNewProductsIds(): Collection
    {
        return collect(Products::getNewProductsIds());
    }
    protected function getPopularProductsIds(): Collection
    {
        return collect(Products::getPopularProductsIds());
    }
}
