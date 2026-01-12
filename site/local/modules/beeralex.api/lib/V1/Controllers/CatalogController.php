<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Main\Engine\Controller;

class CatalogController extends Controller
{
    use ApiProcessResultTrait, Cacheable;

    public function configureActions()
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
        ];
    }

    public function indexAction()
    {
        return $this->process(function () {
            $cacheKey = sprintf(
                'catalog.index.v1|uri:%s',
                $this->getRequest()->getRequestUri() ?? ''
            );
            $cacheSettings = $this->getCacheSettingsDto(
                time: 120,
                key: $cacheKey,
                public: true,
                useEtag: true
            );
            $this->applyHttpCache($cacheSettings);

            service(FileService::class)->includeFile('v1.catalog.index');
            $result = service(ApiResult::class);
            $result->setSeo();
            $this->applyEtag($result, $cacheSettings);
            return $result;
        });
    }
}
