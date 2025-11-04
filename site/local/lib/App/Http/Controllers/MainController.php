<?php
namespace App\Http\Controllers;

use App\Http\GlobalResult;
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

    public function getMenuAction()
    {
        
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
