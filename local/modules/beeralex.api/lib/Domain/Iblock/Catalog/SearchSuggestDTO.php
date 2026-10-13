<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\SectionDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * DTO для подсказок поиска.
 *
 * Два фабричных метода:
 *  - makeInit()    — начальное состояние (теги + популярные/новые товары)
 *  - makeSuggest() — результат поиска по запросу (товары + разделы)
 *
 * @property array{label: string, url: string}[] $tags
 * @property SearchProductDTO[]                  $products
 * @property SectionDTO[]                        $sections
 */
class SearchSuggestDTO extends Resource
{
    /**
     * Начальное состояние саджеста (без поискового запроса).
     *
     * @param array{label: string, url: string}[] $tags     Теги TYPE_MASLA / SOSTAV_MASLA с URL фильтра
     * @param array                               $products Сырые данные товаров
     */
    public static function makeInit(array $tags, array $products): static
    {
        return new static([
            'tags'     => array_values($tags),
            'products' => array_map([SearchProductDTO::class, 'make'], $products),
            'sections' => [],
        ]);
    }

    /**
     * Результат поиска по запросу.
     *
     * @param array $products Сырые данные товаров из CatalogService::search()
     * @param array $sections Сырые данные разделов
     */
    public static function makeSuggest(array $products, array $sections): static
    {
        return new static([
            'tags'     => [],
            'products' => array_map([SearchProductDTO::class, 'make'], $products),
            'sections' => array_map([SectionDTO::class, 'make'], $sections),
        ]);
    }
}
