<?php
declare(strict_types=1);

namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Core\Service\CatalogService;

class OffersRepository extends AbstractCatalogRepository implements OfferRepositoryContract
{
    public function __construct(
        string $iblockCode,
        CatalogService $catalogService
    ) {
        parent::__construct($iblockCode, $catalogService);
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
            $query = $this->catalogService->addCatalogToQuery($query)
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

        $items = $this->findAll(
            ['ID' => $offerIds],
            [
                'ID', 'ACTIVE', 'CATALOG', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CML2_LINK.VALUE'
            ]
        );
        $offers = [];
        foreach ($items as $item) {
            $offers[(int)$item['ID']] = $item;
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
            $productId = (int)($offer['CML2_LINK']['VALUE'] ?? 0);
            if ($productId) {
                $grouped[$productId][] = $offer;
            }
        }

        return $grouped;
    }

    public function getProductsIdsByOffersIds(array $offersIds): array
    {
        if (empty($offersIds)) {
            return [];
        }

        $query = $this->query()
            ->setSelect(['ID', 'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE'])
            ->whereIn('ID', $offersIds);

        $result = $query->exec();
        $map = [];

        while ($row = $result->fetch()) {
            $map[(int)$row['ID']] = (int)$row['PROPERTY_CML2_LINK_VALUE'];
        }

        return $map;
    }
}
