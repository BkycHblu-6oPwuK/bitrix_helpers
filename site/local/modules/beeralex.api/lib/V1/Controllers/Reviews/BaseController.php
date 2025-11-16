<?php
namespace Beeralex\Reviews\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;
use Beeralex\Reviews\Services\ReviewsService;

abstract class BaseController extends Controller
{
    /** @var \Beeralex\Reviews\Services\ReviewsService $service */
    protected $service;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $params = $this->request->get('params') ?? [];
        $product_id = (int)$this->request->get('product_id');
        if(!empty($params)){
            $params = unserialize(base64_decode($params), ['allowed_classes' => false]);
        }
        if(!empty($product_id)){
            $params['PRODUCT_ID'] = $product_id;
        }
        $componentParams = new \Beeralex\Reviews\ComponentParams($params);
        $this->service = new ReviewsService($componentParams);
    }

    public function getDefaultPreFilters(): array
    {
        return [];
    }
}