<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Contracts\StoreRepositoryContract;
use Bitrix\Catalog\StoreTable;
use Beeralex\Core\Repository\Repository;

class StoreRepository extends Repository implements StoreRepositoryContract
{
    public function __construct()
    {
        parent::__construct(StoreTable::class);
    }

    /**
     * @return array
     */
    public function getPickPoints(?array $storeIds = null): array
    {
        $storeData = [];
        $dbList = $this->query()
            ->setSelect(
                [
                    'ID',
                    'TITLE',
                    'ADDRESS',
                    'DESCRIPTION',
                    'IMAGE_ID',
                    'PHONE',
                    'EMAIL',
                    'SCHEDULE',
                    'GPS_N',
                    'GPS_S',
                    'ISSUING_CENTER',
                    'IS_DEFAULT',
                    'SHIPPING_CENTER',
                ]
            )
            ->setFilter([
                'ACTIVE' => 'Y',
            ])
            ->setCacheTtl(86400);

        if ($storeIds) {
            $dbList = $dbList->whereIn('ID', $storeIds);
        }
        $dbList = $dbList->exec();

        while ($store = $dbList->fetch()) {
            if ($store['IMAGE_ID'] > 0) {
                $store['IMAGE'] = \CFile::GetPath($store['IMAGE_ID']);
            } else {
                $store['IMAGE'] = null;
            }
            $storeData[(int)$store['ID']] = $store;
        }
        return $storeData;
    }

    public function getAllIds(): array
    {
        $stores = $this->query()
            ->setSelect(['ID'])
            ->setFilter(['ACTIVE' => 'Y'])
            ->fetchAll();

        return array_map(function ($store) {
            return $store['ID'];
        }, $stores);
    }
}
