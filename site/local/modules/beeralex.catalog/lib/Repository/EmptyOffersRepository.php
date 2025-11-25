<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Core\Repository\IblockRepository;

class EmptyOffersRepository extends IblockRepository implements OfferRepositoryContract
{
    public function __construct() {}

    public function getOfferIdsByProductIds(array $productIds, bool $onlyAvailable = true): array
    {
        return [];
    }

    public function getOffersByIds(array $offerIds): array
    {
        return [];
    }

    public function getOffersByProductIds(array $productIds, bool $onlyAvailable = true): array
    {
        return [];
    }

    public function getProductsIdsByOffersIds(array $offersIds): array
    {
        return [];
    }
}
