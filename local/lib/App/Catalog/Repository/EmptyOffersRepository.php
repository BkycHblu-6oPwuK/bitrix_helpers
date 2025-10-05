<?php
namespace App\Catalog\Repository;

use App\Catalog\Contracts\OfferRepositoryContract;
use Itb\Core\Repository\BaseIblockRepository;

class EmptyOffersRepository extends BaseIblockRepository implements OfferRepositoryContract
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
}
