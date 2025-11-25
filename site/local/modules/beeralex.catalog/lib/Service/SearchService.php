<?php
namespace Beeralex\Catalog\Helper;

use Bitrix\Main\Loader;
use CSearch;
use Beeralex\Core\Model\SectionModel;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\LanguageService;

class SearchService
{
    public function __construct(
        protected readonly IblockService $iblockService,
        protected readonly LanguageService $languageService,
    )
    {
        Loader::includeModule('iblock');
    }
    public function getHints(string $query, int $limit = 50): array
    {
        $result = [];
        $productsIds = self::getProductsIds($query, $limit);

        if (empty($productsIds)) {
            if (static::issetTranslitirate($query)) {
                $query  =$this->languageService->transliterate($query);
                $productsIds = self::getProductsIds($query, $limit);
            } else {
                return $result;
            }
        }

        $sections = self::getSections($productsIds);
        $query = strtolower($query);
        $productsIds = collect($productsIds)->splice(0, 7)->toArray();
        $result['products'] = array_values(CatalogSectionHelper::getProductsForCard($productsIds, true));
        $result['hints'] = $sections;
        return $result;
    }

    public function getProductsIds($query, int $limit)
    {
        Loader::includeModule('search');

        $search = new CSearch;
        $search->SetLimit($limit);
        $search->Search(
            [
                'QUERY' => $query,
                'SITE_ID' => SITE_ID,
                'MODULE_ID' => 'iblock',
                'CHECK_DATES' => 'Y',
                'PARAM2' => $this->iblockService->getIblockIdByCode('catalog'),
            ],
            [
                'RANK' => 'DESC',
                'CUSTOM_RANK' => 'DESC',
            ],
            [
                'STEMMING' => false
            ]
        );
        $productsIds = [];
        while ($element = $search->Fetch()) {
            if (mb_substr($element['ITEM_ID'], 0, 1) === 's') {
                // Это раздел, а не элемент
                continue;
            }

            $productsIds[] = $element['ITEM_ID'];
        }

        return $productsIds;
    }

    protected function getSections($productsIds)
    {
        $catalogId = $this->iblockService->getIblockIdByCode('catalog');
        $section = SectionModel::compileEntityByIblock($catalogId);
        $dbResult = IblockHelper::getElementApiTable($catalogId)::query()
            ->setSelect(
                [
                    'IBLOCK_SECTION_ID',
                    'SECTION_CODE' => 'SECTION.CODE',
                    'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
                    'SECTION_NAME' => 'SECTION.NAME',
                    'SECTION_CUSTOM_NAME' => 'SECTION.UF_CUSTOM_NAME',
                ]
            )
            ->registerRuntimeField('SECTION', [
                'data_type' => $section,
                'reference' => [
                    '=this.IBLOCK_SECTION_ID' => 'ref.ID',
                ],
                'join_type' => 'INNER'
            ])
            ->whereIn('ID', $productsIds)
            ->where('SECTION.ACTIVE', 'Y')
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->exec();

        $sections = [];
        $count = 0;
        while ($count < 5 && $item = $dbResult->Fetch()) {
            $id = (int)$item['IBLOCK_SECTION_ID'];
            if(!$sections[$id]){
                $url = \CIBlock::ReplaceSectionUrl($item['SECTION_PAGE_URL'], $item, false, 'E');
                $item['URL'] = $url;
                $sections[$id] = [
                    'id' => $id,
                    'url' => $item['URL'],
                    'name' => $item['SECTION_CUSTOM_NAME'] ? $item['SECTION_CUSTOM_NAME'] : $item['SECTION_NAME']
                ];
                $count++;
            }
        }
        return array_values($sections);
    }
    protected function issetTranslitirate(string $str): bool
    {
        return preg_match('/[a-zA-Z\[\]\'\'\/\s\;]/', $str) === 1;
    }
}
