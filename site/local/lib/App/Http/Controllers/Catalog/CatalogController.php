<?php

namespace App\Http\Controllers\Catalog;

use App\Http\GlobalResult;
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
