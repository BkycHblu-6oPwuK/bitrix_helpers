<?php

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\GlobalResult;
use Beeralex\Core\Helpers\FilesHelper;
use Bitrix\Main\Engine\Controller;

class CatalogController extends Controller
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
        FilesHelper::includeFile('catalog.index');
        GlobalResult::setSeo();
        GlobalResult::setEmptyPageData();
        return GlobalResult::$result;
    }
}
