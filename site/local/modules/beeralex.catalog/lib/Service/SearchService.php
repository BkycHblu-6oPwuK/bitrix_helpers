<?php
namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Model\SectionTableFactory;
use Beeralex\Core\Service\LanguageService;
use Beeralex\Core\Service\UrlService;

class SearchService
{
    public function __construct(
        protected readonly \CAllSearch $search,
        protected readonly ProductRepositoryContract $productRepository,
        protected readonly CatalogService $catalogService,
        protected readonly LanguageService $languageService,
        protected readonly UrlService $urlService,
        protected readonly SectionTableFactory $sectionTableFactory,
    ) {}

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
        $result['products'] = $this->catalogService->getProductsWithOffers($productsIds);
        $result['hints'] = $sections;
        return $result;
    }

    public function getProductsIds(string $query, int $limit)
    {
        $this->search->SetLimit($limit);
        $this->search->Search(
            [
                'QUERY' => $query,
                'SITE_ID' => SITE_ID,
                'MODULE_ID' => 'iblock',
                'CHECK_DATES' => 'Y',
                'PARAM2' => $this->productRepository->entityId,
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
        while ($element = $this->search->Fetch()) {
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
        $section = $this->sectionTableFactory->compileEntityByIblock($this->productRepository->entityId);
        $dbResult = $this->productRepository->query()
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
                $url = $this->urlService->getSectionUrl($item, $item['SECTION_PAGE_URL'], false, 'E');
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
