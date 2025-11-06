<?php
namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Catalog\Repository\StoreRepository;
use Beeralex\Core\Helpers\CatalogHelper;

class OffersRepository extends IblockRepository implements OfferRepositoryContract
{
    public function __construct()
    {
        parent::__construct('offers');
    }

    public function getOfferIdsByProductIds(array $productIds, bool $onlyAvailable = true): array
    {
        if (empty($productIds)) {
            return [];
        }

        $query = $this->query()
            ->setSelect(['ID', 'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE'])
            ->whereIn('CML2_LINK.VALUE', $productIds);

        if ($onlyAvailable) {
            $query = CatalogHelper::addCatalogToQuery($query)
                ->where('CATALOG.AVAILABLE', 'Y')
                ->where('ACTIVE', 'Y');
        }

        $map = [];
        $result = $query->exec();
        while ($row = $result->fetch()) {
            $map[(int)$row['PROPERTY_CML2_LINK_VALUE']][] = (int)$row['ID'];
        }

        return $map;
    }

    public function getOffersByIds(array $offerIds): array
    {
        if (empty($offerIds)) {
            return [];
        }

        $offers = [];
        $basePrices = [];
        $discountPrices = [];

        $allowedStores = (new StoreRepository())->getAllowedStores();
        $basePriceId = PriceHelper::getBasePriceId();
        $discountPriceId = PriceHelper::getDiscountPriceId();

        $query = CatalogHelper::addStoreToQuery(
            CatalogHelper::addPriceToQuery(
                CatalogHelper::addCatalogToQuery(
                    $this->query()
                )
            )
        )
            ->setSelect([
                'ID',
                'ACTIVE',
                'AVAILABLE' => 'CATALOG.AVAILABLE',
                'PRICE_VALUE' => 'PRICE.PRICE',
                'PRICE_GROUP_ID' => 'PRICE.CATALOG_GROUP_ID',
                'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE',
                'STORE_ID' => 'STORE_PRODUCT.STORE_ID',
                'AMOUNT' => 'STORE_PRODUCT.AMOUNT',
            ])
            ->whereIn('ID', $offerIds);

        $result = $query->exec();
        while ($row = $result->fetch()) {
            $id = (int)$row['ID'];
            $productId = (int)$row['PROPERTY_CML2_LINK_VALUE'];

            $offers[$id] ??= [
                'id' => $id,
                'productId' => $productId,
                'active' => $row['ACTIVE'] === 'Y',
                'available' => $row['AVAILABLE'] === 'Y',
                'storesAvailability' => [],
                'allowedStoresAvailability' => [],
                'price' => null,
            ];

            if ($row['STORE_ID'] && $row['AMOUNT']) {
                $offers[$id]['storesAvailability'][(int)$row['STORE_ID']] = (int)$row['AMOUNT'];
            }

            if ((int)$row['PRICE_GROUP_ID'] === $basePriceId) {
                $basePrices[$id] = (float)$row['PRICE_VALUE'];
            }

            if ((int)$row['PRICE_GROUP_ID'] === $discountPriceId) {
                $discountPrices[$id] = (float)$row['PRICE_VALUE'];
            }
        }

        foreach ($offers as $id => &$offer) {
            $base = $basePrices[$id] ?? 0.0;
            $discount = $discountPrices[$id] ?? 0.0;
            $offer['price'] = PriceHelper::preparePrice($base, $discount);

            foreach ($offer['storesAvailability'] as $storeId => $qty) {
                if (isset($allowedStores[$storeId])) {
                    $offer['allowedStoresAvailability'][$storeId] = $qty;
                }
            }
        }

        return $offers;
    }

    public function getOffersByProductIds(array $productIds, bool $onlyAvailable = true): array
    {
        $map = $this->getOfferIdsByProductIds($productIds, $onlyAvailable);
        $ids = array_merge(...array_values($map));

        if (empty($ids)) {
            return [];
        }

        $offers = $this->getOffersByIds($ids);
        $grouped = [];

        foreach ($offers as $offer) {
            $grouped[$offer['productId']][] = $offer;
        }

        return $grouped;
    }
}
