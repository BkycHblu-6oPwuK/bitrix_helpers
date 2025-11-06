<?php
namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\GlobalResult;
use Beeralex\Core\Helpers\FilesHelper;
use Bitrix\Main\Engine\Controller;

class MainController extends Controller
{
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
        $pathName = $this->normalizePath($pathName);
        FilesHelper::includeFile('index', [
            'pathName' => $pathName,
        ]);

        GlobalResult::setSeo();
        GlobalResult::setEmptyPageData();
        return GlobalResult::$result;
    }

    public function getMenuAction(string $menuType)
    {
        FilesHelper::includeFile('menu', [
            'menuType' => $menuType,
        ]);

        return GlobalResult::$result;
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
