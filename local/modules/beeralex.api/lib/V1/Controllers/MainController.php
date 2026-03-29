<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

class MainController extends Controller
{
    use ApiProcessResultTrait, Cacheable;

    public function configureActions()
    {
        return [
            'getMainPage' => [
                'prefilters' => [],
            ],
            'getMenu' => [
                'prefilters' => [],
            ],
        ];
    }

    public function getMainPageAction()
    {
        return $this->process(function () {
            $cacheSettings = $this->getCacheSettingsDto(
                time: 3600,
                key: 'main_page',
                public: true,
                useEtag: true
            );
            $this->applyHttpCache($cacheSettings);

            service(FileService::class)->includeFile('v1.index');
            $result = service(ApiResult::class);
            $result->setSeo();
            $result->setEmptyPageData();
            $this->applyEtag($result, $cacheSettings);
            return $result;
        });
    }

    public function getMenuAction(string $menuType)
    {
        return $this->process(function () use ($menuType) {
            $cacheSettings = $this->getCacheSettingsDto(
                time: 3600,
                key: 'menu_' . md5($menuType),
                public: true,
                useEtag: true
            );
            $this->applyHttpCache($cacheSettings);

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
            $this->applyEtag($result, $cacheSettings);
            return $result;
        });
    }
}
