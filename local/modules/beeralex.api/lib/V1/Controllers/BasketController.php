<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Basket\BasketDataDTO;
use Beeralex\Catalog\Service\Basket\BasketFactory;
use Beeralex\Catalog\Service\Basket\BasketService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
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
        Loader::requireModule('beeralex.favorite');
        parent::__construct($request);
        $this->basketService = service(BasketFactory::class)->createBasketServiceForCurrentUser();
    }

    public function configureActions()
    {
        return [
            'getIds' => [
                'prefilters' => [],
            ],
            'get' => [
                'prefilters' => [],
            ],
            'add' => [
                'prefilters' => [],
            ],
            'update' => [
                'prefilters' => [],
            ],
            'delete' => [
                'prefilters' => [],
            ],
            'clear' => [
                'prefilters' => [],
            ],
            'applyCoupon' => [
                'prefilters' => [],
            ],
        ];
    }

    public function getIdsAction()
    {
        return $this->process(function () {
            $ids = $this->basketService->getIds();
            $result = \service(ApiResult::class);
            $result->setData(['ids' => $ids]);
            return $result;
        });
    }

    public function getAction()
    {
        return $this->process(function () {
            $basketData = $this->basketService->getBasketData();
            $result = \service(ApiResult::class);
            $result->addPageData(BasketDataDTO::make($basketData), 'basket');
            return $result;
        });
    }

    public function addAction(int $offerId, int $quantity = 1)
    {
        return $this->process(function () use ($offerId, $quantity) {
            $result = $this->basketService->increment($offerId, $quantity);
            if ($result->isSuccess()) {
                $basketData = $this->basketService->getBasketData();
                $result = \service(ApiResult::class);
                $result->addPageData(BasketDataDTO::make($basketData), 'basket');
                return $result;
            }
            return $result;
        });
    }

    public function updateAction(int $offerId, int $quantity = 1)
    {
        return $this->process(function () use ($offerId, $quantity) {
            $result = $this->basketService->changeProductQuantityInBasket($offerId, $quantity);
            if ($result->isSuccess()) {
                $basketData = $this->basketService->getBasketData();
                $result = \service(ApiResult::class);
                $result->addPageData(BasketDataDTO::make($basketData), 'basket');
                return $result;
            }
            return $result;
        });
    }

    public function deleteAction(int $offerId)
    {
        return $this->process(function () use ($offerId) {
            $result = $this->basketService->remove($offerId);
            if ($result->isSuccess()) {
                $basketData = $this->basketService->getBasketData();
                $result = \service(ApiResult::class);
                $result->addPageData(BasketDataDTO::make($basketData), 'basket');
                return $result;
            }
            return $result;
        });
    }

    public function clearAction()
    {
        return $this->process(function () {
            $result = $this->basketService->removeAll();
            if ($result->isSuccess()) {
                $this->setCookie('cart_coupon', '', time() - 3600);
                
                $basketData = $this->basketService->getBasketData();
                $result = \service(ApiResult::class);
                $result->addPageData(BasketDataDTO::make($basketData), 'basket');
                return $result;
            }
            return $result;
        });
    }

    public function applyCouponAction(string $coupon)
    {
        return $this->process(function () use ($coupon) {
            $result = $this->basketService->applyCoupon($coupon);
            if ($result->isSuccess()) {
                $this->setCookie('cart_coupon', $coupon, time() + 60 * 60 * 24 * 30);
                
                $basketData = $this->basketService->getBasketData();
                $result = \service(ApiResult::class);
                $result->addPageData(BasketDataDTO::make($basketData), 'basket');
                return $result;
            }
            return $result;
        });
    }

    /**
     * Установка куки для SPA режима
     */
    private function setCookie(
        string $name,
        string $value,
        int $expires,
        string $path = '/'
    ): void {
        global $APPLICATION;

        $APPLICATION->set_cookie(
            $name,
            $value,
            $expires,
            $path,
            false,
            false, // httpOnly = false для доступа из JS
            true,  // secure в production
            false,
            true   // samesite = strict
        );
    }
}
