<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

class MainController extends Controller
{
    use ApiProcessResultTrait;

    public function configureActions()
    {
        return [
            'getContent' => [
                'prefilters' => [],
            ],
            'getMenu' => [
                'prefilters' => [],
            ],
        ];
    }

    public function getContentAction(string $code)
    {
        return $this->process(function () use ($code) {
            service(FileService::class)->includeFile('v1.index', [
                'code' => $code,
            ]);
            $result = service(ApiResult::class);
            $result->setSeo();
            $result->setEmptyPageData();
            return $result;
        });
    }

    public function getMenuAction(string $menuType)
    {
        return $this->process(function () use ($menuType) {
            $result = service(ApiResult::class);
            $iblockId = 0;
            switch ($menuType) {
                case 'catalog':
                    $iblockId = (int)service(\Beeralex\Core\Service\IblockService::class)
                        ->getIblockIdByCode('catalog');
                    break;
                default:
                    $result->addError(new Error("Unknown menu type - {$menuType}", 'menu'));
            }

            if ($iblockId) {
                service(FileService::class)->includeFile('v1.menu', [
                    'iblockId' => $iblockId,
                ]);
            }

            return $result;
        });
    }
}
