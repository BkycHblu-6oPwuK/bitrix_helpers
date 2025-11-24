<?php

use Illuminate\Support\Collection;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\QueryService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexContent extends \CBitrixComponent
{
    public function executeComponent()
    {
        if (!$this->arParams['PATH']) {
            throw new \RuntimeException("PATH value in arParams is required");
        }
        if ($this->startResultCache()) {
            $this->arResult = $this->getContent();
            $this->endResultCache();
        }
        $this->includeComponentTemplate();
    }

    protected function getContent(): array
    {
        $contentIblockId = service(IblockService::class)->getIblockIdByCode('content');
        $query = service(IblockService::class)->addSectionModelToQuery($contentIblockId, service(IblockService::class)->getElementApiTable($contentIblockId)::query())
        ->where('ACTIVE', 'Y')
        ->where('IBLOCK_MODEL_SECTION.UF_URL', $this->arParams['PATH'])
        ->setSelect(
            [
                'ID',
                'NAME',
                'PRODUCTS_TITLE.VALUE',
                'PRODUCTS_IDS.VALUE',
                'PRODUCTS_TYPE.ITEM.XML_ID',
                'TYPE.ITEM.XML_ID',
                'LINK.VALUE',
                'PREVIEW_VIDEO.VALUE',
                'VIDEO_TITLE.VALUE',
                'VIDEO_TEXT.VALUE',
                'VIDEO_LINK.VALUE',
                'ARTICLES_IDS.VALUE',
                'ARTICLES_TITLE.VALUE',
                'MAIN_BANNER.VALUE',
                'IBLOCK_MODEL_SECTION.UF_URL',
                'FORM_ID.VALUE'
            ]
        )
            ->setOrder(['SORT' => 'ASC']);
        return collect(service(QueryService::class)->fetchGroupedEntities($query))->map(function ($item) {
            $type = $item['TYPE']['ITEM']['XML_ID'];
            switch ($type) {
                case ContentTypes::SLIDER->value:
                    $ids = match ($item['PRODUCTS_TYPE']) {
                        ContentTypes::PRODUCTS_NEW->value => $this->getNewProductsIds(),
                        ContentTypes::PRODUCTS_POPULAR->value => $this->getPopularProductsIds(),
                        default => null
                    };

                    return [
                        'type' =>  ContentTypes::SLIDER,
                        'ids' => $ids ?? array_column($item['PRODUCTS_IDS'], 'VALUE'),
                        'title' => $item['TITLE'] ? $item['TITLE'] : $item['NAME'],
                        'link' => $item['LINK']['VALUE'],
                    ];
                case ContentTypes::MAIN_BANNER->value:
                    return [
                        'type' => ContentTypes::MAIN_BANNER,
                        'ids' => array_column($item['MAIN_BANNER'], 'VALUE'),
                    ];
                case ContentTypes::VIDEO->value:
                    return [
                        'type' =>  ContentTypes::VIDEO,
                        'video_preview_id' => $item['VIDEO_PREVIEW_ID'],
                        'title' => $item['VIDEO_TITLE'] ? $item['VIDEO_TITLE'] : $item['NAME'],
                        'text' => $item['VIDEO_TEXT'] ? unserialize($item['VIDEO_TEXT'])['TEXT'] : '',
                        'video_link' => $item['VIDEO_LINK'],
                    ];
                case ContentTypes::ARTICLES->value:
                    return [
                        'type' =>  ContentTypes::ARTICLES,
                        'ids' => array_column($item['ARTICLES_IDS'], 'VALUE'),
                        'title' => $item['ARTICLES_TITLE'] ? $item['ARTICLES_TITLE'] : $item['NAME'],
                        'link' => $item['LINK']['VALUE'],
                    ];
                case ContentTypes::FORM->value :
                    return [
                        'type' => ContentTypes::FORM,
                        'id' => $item['FORM_ID']['VALUE']
                    ];
                default:
                    return [];
            }
        })->toArray();
    }

    protected function getNewProductsIds(): Collection
    {
        return collect(service(CatalogService::class)->getNewProductsIds());
    }
    protected function getPopularProductsIds(): Collection
    {
        return collect(service(CatalogService::class)->getPopularProductsIds());
    }
}
