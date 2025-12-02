<?php
declare(strict_types=1);

namespace App\Repository;

use Beeralex\Catalog\Repository\OffersRepository as CatalogOffersRepository;

class OffersRepository extends CatalogOffersRepository
{
    public function getOffersByIds(array $offerIds): array
    {
        if (empty($offerIds)) {
            return [];
        }

        $items = $this->findAll(
            ['ID' => $offerIds],
            [
                'ID', 'ACTIVE', 'CATALOG', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CML2_LINK', 'COLOR_REF', 'SIZES_SHOES', 'SIZES_CLOTHES'
            ]
        );
        $offers = [];
        foreach ($items as $item) {
            $offers[(int)$item['ID']] = $item;
        }

        return $offers;
    }
}
