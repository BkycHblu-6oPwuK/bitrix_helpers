<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Catalog\SearchSuggestDTO;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Service\CatalogService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class SearchController extends Controller
{
    use ApiProcessResultTrait;

    public function configureActions(): array
    {
        return [
            'suggest' => ['prefilters' => []],
            'init'    => ['prefilters' => []],
        ];
    }

    public function initAction(): array
    {
        return $this->process(function () {
            Loader::includeModule('beeralex.catalog');
            $result  = service(ApiResult::class);
            $catalog = service(CatalogService::class);
            /** @var ProductRepositoryContract $repo */
            $repo = service(DIServiceKey::PRODUCT_REPOSITORY->value);
            $tags = $this->getTags($repo->entityId);
            $popularIds = $repo->getPopularProductsIds(10, 86400);
            $newIds     = $repo->getNewProductsIds(10, 86400);
            $availableIds = $repo->getAvailableProductIds([], 10, 86400);
            $merged = array_values(array_unique(array_merge($popularIds, $newIds, $availableIds)));
            $merged = array_slice($merged, 0, 10);
            $products = empty($merged)
                ? []
                : $catalog->getProductsWithOffers($merged);

            $result->setData(
                SearchSuggestDTO::makeInit($tags, array_values($products))
            );

            return $result;
        });
    }

    /**
     * Поиск по запросу: товары + разделы
     */
    public function suggestAction(string $query): array
    {
        return $this->process(function () use ($query) {
            Loader::includeModule('beeralex.catalog');
            $result  = service(ApiResult::class);
            $catalog = service(CatalogService::class);

            $searchResult = $catalog->search(trim($query), 8);

            $result->setData(
                SearchSuggestDTO::makeSuggest(
                    array_values($searchResult['PRODUCTS'] ?? []),
                    $searchResult['SECTIONS'] ?? []
                )
            );

            return $result;
        });
    }

    /**
     * Собирает уникальные теги из свойств TYPE_MASLA и SOSTAV_MASLA через компонент
     * catalog.smart.filter.
     *
     * @return array{label: string, url: string}[]
     */
    private function getTags(int $iblockId): array
    {
        \CBitrixComponent::includeComponentClass('beeralex:catalog.smart.filter');
        $tags = [];
        $items = \BeeralexCatalogSmartFilter::makeUrlsForFilters([
            'IBLOCK_ID'          => $iblockId,
            'FILTER_NAME'        => 'arrFilter',
            'SEF_MODE'           => 'Y',
            'SEF_RULE'           => '/bitrix/api/v1/catalog/filter/#SMART_FILTER_PATH#/apply/'
        ], ['BRAND_REF']);

        foreach ($items as $item) {
            foreach ($item['VALUES'] as $valueItem) {
                if (empty($valueItem['URL']) || empty($valueItem['VALUE'])) {
                    continue;
                }
                $url = $valueItem['URL'];

                $tags[] = [
                    'label' => htmlspecialchars_decode((string)$valueItem['VALUE']),
                    'url'   => $url,
                ];
            }
        }

        return $tags;
    }
}
