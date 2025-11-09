<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\GlobalResult;
use Beeralex\Core\Helpers\FilesHelper;
use Bitrix\Main\Engine\Controller;

class ArticlesController extends Controller
{
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
        FilesHelper::includeFile('v1.articles.index');
        GlobalResult::setSeo();
        return GlobalResult::$result;
    }
}
