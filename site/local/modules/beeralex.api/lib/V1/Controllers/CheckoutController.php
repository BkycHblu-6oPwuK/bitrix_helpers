<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Bitrix\Main\Engine\Controller;

class CheckoutController extends Controller
{
    use ApiProcessResultTrait;

    public function configureActions()
    {
        return [
            'get' => [
                'prefilters' => [],
            ],
            'refresh' => [
                'prefilters' => [],
            ],
            'store' => [
                'prefilters' => [],
            ],
        ];
    }

    public function getAction()
    {
        return $this->process(function () {
            \service(FileService::class)->includeFile('v1.checkout.index');
            return \service(ApiResult::class);
        });
    }

    public function refreshAction()
    {
        return $this->process(function () {
            $request = $this->getRequest();
            $request->set('via_ajax', 'Y');
            $request->set('soa-action', 'refreshOrderAjax');
            \service(FileService::class)->includeFile('ajax', [], '/local/components/beeralex/sale.order.ajax/');
            return \service(ApiResult::class);
        });
    }

    public function storeAction()
    {
        return $this->process(function () {
            $request = $this->getRequest();
            $request->set('via_ajax', 'Y');
            $request->set('soa-action', 'saveOrderAjax');
            \service(FileService::class)->includeFile('ajax', [], '/local/components/beeralex/sale.order.ajax/');
            return \service(ApiResult::class);
        });
    }
}
