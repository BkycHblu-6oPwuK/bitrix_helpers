<?php
namespace Beeralex\Catalog\Helper;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Beeralex\Catalog\Helper\SortingHelper;

class CatalogSectionHelper
{

    /**
     * Получает инфу для карточки товара в каталоге
     *
     * @param array $productIds
     *
     * @return array
     */
    public static function getProductsForCard(array $productIds, $isAvailable = true): array
    {
        //$products = array_values(ProductsHelper::getProductsAndOffers($productIds, $isAvailable));
        return [];
    }


    /**
     * Сортировка для vue
     *
     * @return array
     */
    public static function getSorting(): array
    {
        $currentSortId = SortingHelper::getRequestedSortIdOrDefault();
        $availableSorting = SortingHelper::getAvailableSortings();
        $sortTitle = $availableSorting[$currentSortId]['name'];
        return [
            'currentSortId'    => $currentSortId,
            'defaultSortId'    => SortingHelper::getDefaultSortId(),
            'title'            => $sortTitle,
            'availableSorting' => array_values($availableSorting)
        ];
    }


    /**
     * Добавляет в URL параметры запрошенной сортировки и поисковый запрос
     *
     * @param string $url
     *
     * @return string
     */
    public static function makeUrl(string $url): string
    {
        $requestedSortId = SortingHelper::getRequestedSortIdOrDefault();
        $query = Context::getCurrent()->getRequest()->get('q');

        $uri = new Uri($url);

        if ($requestedSortId != SortingHelper::getDefaultSortId()) {
            $uri->addParams(['sort' => $requestedSortId]);
        }
        if ($query) {
            $uri->addParams(['q' => $query]);
        }

        return $uri->getUri();
    }

    public static function getPath(int $sectionId)
    {
        // $sections = [];

        // $sectionEntity = CatalogHelper::getCatalogSectionsEntity();
        // while ($sectionId) {
        //     $res = $sectionEntity::query()->setSelect(['IBLOCK_SECTION_ID', 'NAME', 'CODE', 'ID', 'UF_CUSTOM_NAME', 'ACTIVE'])->where('ID', $sectionId)->setCacheTtl(86400)->cacheJoins(true)->exec()->fetch();
        //     if($res['ACTIVE'] === 'Y') {
        //         $sections[] = [
        //             'id' => $res['ID'],
        //             'title' => $res['UF_CUSTOM_NAME'] ? $res['UF_CUSTOM_NAME'] : $res['NAME'],
        //             'code' => $res['CODE'],
        //         ];
        //     }

        //     if (!empty($res['IBLOCK_SECTION_ID'])) {
        //         $sectionId = $res["IBLOCK_SECTION_ID"];
        //     } else {
        //         break;
        //     }
        // }
        // $sections = array_reverse($sections);
        // $parentPath = PageHelper::getCatalogPageUrl();
        // foreach ($sections as &$section) {
        //     $section['url'] = $parentPath . $section['code'] . '/';
        //     $parentPath = $section['url'];
        // }

        // return $sections;
    }
}
