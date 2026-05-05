<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Catalog\CatalogSearchDTO;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class CatalogController extends Controller
{
    use ApiProcessResultTrait, Cacheable;

    public function configureActions()
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
            'search' => [
                'prefilters' => [],
            ],
        ];
    }

    public function indexAction()
    {
        return $this->process(function () {
            service(FileService::class)->includeFile('v1.catalog.index');
            $result = service(ApiResult::class);
            $result->setSeo();
            return $result;
        });
    }

    public function searchAction(string $query)
    {
        Loader::includeModule('beeralex.catalog');
        $catalogService = service(CatalogService::class);
        return $this->process(function () use ($catalogService, $query) {
            $result = service(ApiResult::class);
            $resultSearch = $catalogService->search($query);
            $result->setData(CatalogSearchDTO::make($resultSearch));
            return $result;
        });
    }
}
