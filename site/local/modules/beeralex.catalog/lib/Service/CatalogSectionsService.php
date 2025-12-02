<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Service\UrlService;

class CatalogSectionsService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productsRepository,
        protected readonly UrlService $urlService
    ) {}

    public function getSections(array $productsIds)
    {
        $dbResult = $this->productsRepository->query()
            ->setSelect(
                [
                    'IBLOCK_SECTION_ID',
                    'SECTION_CODE' => 'SECTION.CODE',
                    'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
                    'SECTION_NAME' => 'SECTION.NAME',
                ]
            )
            ->registerRuntimeField('SECTION', [
                'data_type' => $this->productsRepository->getIblockSectionRepository()->getEntityClass(),
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
            if (!$sections[$id]) {
                $url = $this->urlService->getSectionUrl($item, $item['SECTION_PAGE_URL'], false, 'E');
                $item['URL'] = $url;
                $sections[$id] = [
                    'ID' => $id,
                    'URL' => $item['URL'],
                    'NAME' => $item['SECTION_NAME']
                ];
                $count++;
            }
        }
        return array_values($sections);
    }
}
