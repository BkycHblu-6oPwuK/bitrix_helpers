<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;

class FormController extends Controller
{
    use ApiProcessResultTrait;

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
        return $this->process(function () use ($formId) {
            service(FileService::class)->includeFile('v1.form.index', [
                'formId' => $formId
            ]);
            return service(ApiResult::class);
        });
    }

    public function storeAction(int $formId)
    {
        return $this->process(function () use ($formId) {
            $_POST['WEB_FORM_ID'] = $formId;
            $_REQUEST['web_form_apply'] = 'Y';
            service(FileService::class)->includeFile('v1.form.index', [
                'formId' => $formId
            ]);
            return service(ApiResult::class);
        });
    }
}
