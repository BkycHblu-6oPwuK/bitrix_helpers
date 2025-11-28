<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Service\LanguageService;

class SearchService
{
    public const REQUEST_PARAM = 'q';

    public function __construct(
        protected readonly \CAllSearch $search,
        protected readonly ProductRepositoryContract $productRepository,
        protected readonly LanguageService $languageService,
    ) {}

    public function getProductsIds(string $query, int $limit): array
    {
        $this->search->SetLimit($limit);
        $getProductsIds = function (string $query): array {
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

                $productsIds[] = (int)$element['ITEM_ID'];
            }

            return $productsIds;
        };

        $productsIds = $getProductsIds($query);

        if (empty($productsIds)) {
            if ($this->issetTranslitirate($query)) {
                $productsIds = $getProductsIds($this->languageService->transliterate($query));
            }
        }
        return $productsIds;
    }

    protected function issetTranslitirate(string $str): bool
    {
        return preg_match('/[a-zA-Z\[\]\'\'\/\s\;]/', $str) === 1;
    }
}
