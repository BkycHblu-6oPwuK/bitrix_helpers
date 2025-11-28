<?php
declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Catalog\Service\Basket\BasketFactory;
use Beeralex\Catalog\Service\Basket\BasketService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;

/**
 * контроллер корзины, пусть использует basketFacade для действий над добавлением, удалением, получением и т.д.
 */
class BasketController extends Controller
{
    use ApiProcessResultTrait;

    protected readonly BasketService $basketService;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->basketService = service(BasketFactory::class)->createBasketServiceForCurrentUser();
    }
    
    public function configureActions()
    {
        return [
            'getBasket' => [
                '-prefilters' => [],
            ],
            'add' => [
                '-prefilters' => [],
            ],
            'update' => [
                '-prefilters' => [],
            ],
            'delete' => [
                '-prefilters' => [],
            ],
            'clear' => [
                '-prefilters' => [],
            ],
            'applyCoupon' => [
                '-prefilters' => [],
            ],
        ];
    }

    public function getBasketAction()
    {
        return $this->process(function () {
            return $this->basketService->getBasketData();
        });
    }

    public function addAction(int $offerId, int $quantity = 1)
    {
        return $this->process(function () use ($offerId, $quantity) {
            $result = $this->basketService->increment($offerId, $quantity);
            if ($result->isSuccess()) {
                return $this->basketService->getBasketData();
            }
            return $result;
        });
    }

    public function updateAction(int $offerId, int $quantity)
    {
        return $this->process(function () use ($offerId, $quantity) {
            $result = $this->basketService->changeProductQuantityInBasket($offerId, $quantity);
            if ($result->isSuccess()) {
                return $this->basketService->getBasketData();
            }
            return $result;
        });
    }

    public function deleteAction(int $offerId)
    {
        return $this->process(function () use ($offerId) {
            $result = $this->basketService->remove($offerId);
            if ($result->isSuccess()) {
                return $this->basketService->getBasketData();
            }
            return $result;
        });
    }

    public function clearAction()
    {
        return $this->process(function () {
            $result = $this->basketService->removeAll();
            if ($result->isSuccess()) {
                return $this->basketService->getBasketData();
            }
            return $result;
        });
    }

    public function applyCouponAction(string $coupon)
    {
        return $this->process(function () use ($coupon) {
            $result = $this->basketService->applyCoupon($coupon);
            if ($result->isSuccess()) {
                return $this->basketService->getBasketData();
            }
            return $result;
        });
    }
}
