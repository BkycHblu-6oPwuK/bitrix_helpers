<?php

namespace Beeralex\Catalog\Service\Basket;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Repository\FuserRepository;
use Beeralex\Catalog\Service\Discount\CouponsService;
use Beeralex\Catalog\Service\Discount\DiscountFactory;
use Beeralex\Catalog\Service\PriceService;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Fuser;

class BasketFactory
{
    public function __construct(
        protected readonly string $siteId,
        protected readonly DiscountFactory $discountFactory,
        protected readonly FuserRepository $fuserRepository
    ) {}

    public function createBasketForCurrentUser(): BasketBase
    {
        return Basket::loadItemsForFUser(Fuser::getId(), $this->siteId);
    }

    public function createBasket(int $userId): ?BasketBase
    {
        $fUserId = (int)$this->fuserRepository->getByUserId($userId, $this->siteId, ['ID'])['ID'] ?? 0;
        if ($fUserId) {
            return Basket::loadItemsForFUser($fUserId, $this->siteId);
        }
        return null;
    }

    public function createBasketService(BasketBase $basket): BasketService
    {
        return new BasketService(
            $basket,
            $this->createBasketUtils(
                service(ProductRepositoryContract::class),
                service(OfferRepositoryContract::class),
                $basket,
                service(PriceService::class)
            ),
            service(CouponsService::class),
            $this->discountFactory->createDiscount($basket),
            service(PriceService::class)
        );
    }

    public function createBasketServiceForCurrentUser(): BasketService
    {
        return $this->createBasketService($this->createBasketForCurrentUser());
    }

    public function createBasketUtils(
        ProductRepositoryContract $productsRepository,
        OfferRepositoryContract $offersRepository,
        BasketBase $basket,
        PriceService $priceService
    ): BasketUtils {
        return new BasketUtils(
            $productsRepository,
            $offersRepository,
            $basket,
            $priceService
        );
    }
}
