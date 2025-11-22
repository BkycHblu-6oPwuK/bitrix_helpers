<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;

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

    public function getContentAction(string $pathName)
    {
        return $this->process(function () use ($pathName) {
            $pathName = $this->normalizePath($pathName);
            service(FileService::class)->includeFile('v1.index', [
                'pathName' => $pathName,
            ]);

            service(ApiResult::class)->setSeo();
            service(ApiResult::class)->setEmptyPageData();
            return service(ApiResult::class);
        });
    }

    public function getMenuAction(string $menuType)
    {
        return $this->process(function () use ($menuType) {
            service(FileService::class)->includeFile('v1.menu', [
                'menuType' => $menuType,
            ]);

            return service(ApiResult::class);
        });
    }
    
    /**
     * Приводит путь к виду:
     * - всегда начинается со слеша (/)
     * - не заканчивается слешем (/), кроме корня
     */
    protected function normalizePath(string $path): string
    {
        $path = mb_trim($path);
        $path = preg_replace('#/+#', '/', $path);

        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        if (mb_strlen($path) > 1) {
            $path = mb_rtrim($path, '/');
        }

        return $path;
    }
}
