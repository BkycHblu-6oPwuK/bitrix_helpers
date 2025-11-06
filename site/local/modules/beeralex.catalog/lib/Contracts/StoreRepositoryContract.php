<?php

namespace Beeralex\Catalog\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface StoreRepositoryContract extends RepositoryContract
{
    /**
     * @return \Beeralex\Catalog\Dto\PickPointDTO[]
     */
    public function getPickPoints(?array $storeIds = null): array;
    public function getAllowedStores(): array;
    public function getAllowedStoresWithSizes($offers): array;
    public function getAllIds(): array;
}
