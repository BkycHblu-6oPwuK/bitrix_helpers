<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Service\LanguageService;

class SearchService
{
    public const REQUEST_PARAM = 'q';

    /**
     * @param callable|null $factoryIncludeComponent Фабрика для подключения компонента поиска (возвращает массив с ID элементов)
     */
    public function __construct(
        protected $factoryIncludeComponent,
        protected readonly ProductRepositoryContract $productRepository,
        protected readonly LanguageService $languageService,
    ) {}

    /**
     * @return array{0: array, 1: array} Массив с ID товаров и массив с ID разделов
     */
    public function getIds(string $query, int $limit): array
    {
        $_REQUEST[static::REQUEST_PARAM] = $query;
        $getIds = function (): array {
            if(empty($this->factoryIncludeComponent) || !is_callable($this->factoryIncludeComponent)) {
                return [[], []];
            }
            ob_start();
            $arElements = ($this->factoryIncludeComponent)();
            ob_end_clean();
            $productsIds = [];
            $sectionIds = [];
            foreach ($arElements as $key => $elementId) {
                if (mb_strtolower(mb_substr($elementId, 0, 1)) === 's') {
                    $sectionIds[] = (int)mb_substr($elementId, 1);
                    continue;
                }

                $productsIds[] = $elementId;
            }
            return [$productsIds, $sectionIds];
        };

        [$productsIds, $sectionIds] = $getIds();

        if (empty($productsIds)) {
            if ($this->issetTranslitirate($query)) {
                $_REQUEST[static::REQUEST_PARAM] = $this->languageService->transliterate($query);
                [$productsIds, $sectionIds] = $getIds();
            }
        }
        return [
            array_slice($productsIds, 0, $limit),
            array_slice($sectionIds, 0, $limit),
        ];
    }

    protected function issetTranslitirate(string $str): bool
    {
        return preg_match('/[a-zA-Z\[\]\'\'\/\s\;]/', $str) === 1;
    }
}
