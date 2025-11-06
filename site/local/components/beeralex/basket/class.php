<?php

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Beeralex\Catalog\Basket\BasketFacade;
use App\Main\PageHelper;

class BeeralexBasket extends \CBitrixComponent implements Controllerable
{
    private $basketFacade;

    public function __construct($component = null)
    {
        parent::__construct($component);
        Loader::includeModule("sale");
        Loader::includeModule("catalog");
        $this->basketFacade = BasketFacade::getForCurrentUser();
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function configureActions(): array
    {
        return [
            'getCart' => ['prefilters' => [new Csrf]],
            'addCoupon' => ['prefilters' => [new Csrf]],
            'add' => ['prefilters' => [new Csrf]],
            'removeOne' => ['prefilters' => [new Csrf]],
            'remove' => ['prefilters' => [new Csrf]],
        ];
    }

    public function getCartAction($isDetail): array
    {
        $isDetail = (bool)$isDetail;
        return $this->getSuccessResult($isDetail);
    }

    /**
     * @param string $couponCode
     */
    public function addCouponAction($couponCode): array
    {
        try {
            $this->basketFacade->applyCoupon($couponCode);
        } catch (\Exception $e) {}
        return $this->getSuccessResult(true);
    }

    public function addAction(int $productId, $isDetail): array
    {
        $isDetail = (bool)$isDetail;
        try {
            $this->basketFacade->add($productId)->save();
            return $this->getSuccessResult($isDetail);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function removeOneAction(int $productId, $isDetail): array
    {
        $isDetail = (bool)$isDetail;
        try {
            $this->basketFacade->removeOne($productId)->save();
            return $this->getSuccessResult($isDetail);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function removeAction(int $productId, $isDetail): array
    {
        $isDetail = (bool)$isDetail;
        try {
            $this->basketFacade->remove($productId)->save();
            return $this->getSuccessResult($isDetail);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getSuccessResult(bool $isDetail)
    {
        if($isDetail){
            $result = $this->basketFacade->getBasketData();
            $result['success'] = true;
            $result['checkoutUrl'] = PageHelper::getCheckoutPageUrl();
        } else {
            $result = [
                'items' => [],
                'coupon' => '',
                'summary' => [
                    'totalQuantity' => $this->basketFacade->getOffersQuantity(),
                ],
                'success' => true
            ];
        }
        return $result;
    }
}
