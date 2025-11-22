<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;

class ArticlesController extends Controller
{
    use ApiProcessResultTrait;
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
            service(FileService::class)->includeFile('v1.articles.index');
            service(ApiResult::class)->setSeo();
            return service(ApiResult::class);
        });
    }
}
