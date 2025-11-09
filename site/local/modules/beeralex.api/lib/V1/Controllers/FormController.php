<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\GlobalResult;
use Beeralex\Core\Helpers\FilesHelper;
use Bitrix\Main\Engine\Controller;

class FormController extends Controller
{
    public function configureActions()
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
            'store' => [
                'prefilters' => [],
            ],
        ];
    }

    public function indexAction(int $formId)
    {
        FilesHelper::includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return GlobalResult::$result;
    }

    public function storeAction(int $formId)
    {
        $_POST['WEB_FORM_ID'] = $formId;
        $_REQUEST['web_form_apply'] = 'Y';
        FilesHelper::includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return GlobalResult::$result;
    }
}
