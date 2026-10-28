<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;

class ContentController extends Controller
{
    use ApiProcessResultTrait;

    protected readonly FileService $fileService;

    public function __construct(?Request $request = null)
    {
        $this->fileService = service(FileService::class);
        parent::__construct($request);
    }

    public function configureActions()
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
        ];
    }

    public function indexAction(string $code)
    {
        return $this->process(function () use ($code) {
            $this->fileService->includeFile('v1.content.index', ['code' => $code]);
            $result = service(ApiResult::class);
            $result->setSeo();
            return $result;
        });
    }
}
