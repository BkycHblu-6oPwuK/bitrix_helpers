<?php

namespace Beeralex\Catalog\Service\Basket;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Enum\DIServiceKey;
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

    public function createBasketService(
        BasketBase $basket,
        ?ProductRepositoryContract $productsRepository = null,
        ?OfferRepositoryContract $offersRepository = null
    ): BasketService
    {
        return new BasketService(
            $basket,
            $this->createBasketUtils(
                $productsRepository,
                $offersRepository,
                $basket,
                service(PriceService::class)
            ),
            service(CouponsService::class),
            $this->discountFactory->createDiscount($basket),
            service(PriceService::class)
        );
    }

    public function createBasketServiceForCurrentUser(
        ?ProductRepositoryContract $productsRepository = null,
        ?OfferRepositoryContract $offersRepository = null
    ): BasketService
    {
        return $this->createBasketService($this->createBasketForCurrentUser(), $productsRepository, $offersRepository);
    }

    public function createBasketUtils(
        ?ProductRepositoryContract $productsRepository = null,
        ?OfferRepositoryContract $offersRepository = null,
        BasketBase $basket,
        PriceService $priceService
    ): BasketUtils {
        return new BasketUtils(
            $productsRepository ?? service(DIServiceKey::PRODUCT_REPOSITORY->value),
            $offersRepository ?? service(DIServiceKey::OFFERS_REPOSITORY->value),
            $basket,
            $priceService
        );
    }
}
