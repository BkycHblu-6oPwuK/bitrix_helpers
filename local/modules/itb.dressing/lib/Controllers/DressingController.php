<?php

namespace Itb\Dressing\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Itb\Dressing\Services\DressingService;
use Itb\Helpers\PageHelper;

class DressingController extends Controller
{
    protected readonly DressingService $service;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->service = new DressingService();
    }

    public function configureActions()
    {
        return [
            'toggle' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'get' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'createOrder' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
        ];
    }

    public function toggleAction(int $offerId, $isDetail)
    {
        $isDetail = (bool)$isDetail;
        try {
            $action = $this->service->toggleBasketItem($offerId);
            $result = $this->getSuccessResult($isDetail);
            $result['action'] = $action;
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getAction($isDetail)
    {
        $isDetail = (bool)$isDetail;
        try {
            return $this->getSuccessResult($isDetail);
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public function createOrderAction($form)
    {
        try {
            $form = Json::decode($form);
            $order = $this->service->make($form);
            return [
                'success' => true,
                'orderId' => $order->getId(),
                'redirectUrl' => (new Uri(PageHelper::getDressingUrl()))->addParams([
                    'ORDER_ID' => $order->getId()
                ]),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function getSuccessResult(bool $isDetail)
    {
        if ($isDetail) {
            $result = $this->service->basketFacade->getBasketData();
            $result['form'] = $this->service->getDefaultForm();
            $result['success'] = true;
        } else {
            $result = [
                'items' => [],
                'summary' => [
                    'totalQuantity' => $this->service->basketFacade->getOffersQuantity(),
                ],
                'form' => [],
                'success' => true
            ];
        }
        return $result;
    }
}
