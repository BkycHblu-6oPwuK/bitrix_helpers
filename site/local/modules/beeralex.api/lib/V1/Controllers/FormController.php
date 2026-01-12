<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Form\FormService;
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\Core\Service\FileService;

class FormController extends ApiController
{
    use ApiProcessResultTrait;
    protected readonly FormService $formService;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->formService = service(FormService::class);
    }

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

    /*public function storeQuestionAction(QuestionRequestDTO $requestDto)
    {
        return $this->process(function () use ($requestDto) {
            return $this->formService->submitQuestionForm($requestDto);
        });
    }*/
}
