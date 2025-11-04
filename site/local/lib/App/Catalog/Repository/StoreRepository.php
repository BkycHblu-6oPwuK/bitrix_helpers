<?php
namespace App\Catalog\Repository;

use App\Catalog\Contracts\StoreRepositoryContract;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Loader;
use App\Catalog\Dto\PickPointDTO;
use Beeralex\Core\Repository\Repository;

Loader::includeModule('catalog');

class StoreRepository extends Repository implements StoreRepositoryContract
{
    public function __construct()
    {
        parent::__construct(StoreTable::class);
    }

    /**
     * @return PickPointDTO[]
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
                    'SCHEDULE',
                    'GPS_N',
                    'GPS_S',
                    'ISSUING_CENTER'
                ]
            )
            ->setFilter([
                'ACTIVE' => 'Y',
                'ISSUING_CENTER' => 'Y',
                'SHIPPING_CENTER' => 'Y',
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
            $dto = new PickPointDTO;
            $dto->id = (int)$store['ID'];
            $dto->name = $store['TITLE'];
            $dto->address = $store['ADDRESS'];
            $dto->description = $store['DESCRIPTION'];
            if ($store['IMAGE']) {
                $dto->images[] = $store['IMAGE'];
            }
            $dto->phone = $store['PHONE'];
            $dto->schedule = $store['SCHEDULE'];
            $dto->location = [
                'latitude' => $store['GPS_N'],
                'longitude' => $store['GPS_S']
            ];
            $storeData[$store['ID']] = $dto;
        }
        return $storeData;
    }

    public function getAllowedStores(): array
    {
        $stores = $this->query()
            ->setSelect([
                'ID',
                'ADDRESS',
                'PHONE',
                'SCHEDULE',
                'DESCRIPTION',
            ])
            ->setFilter([
                'ACTIVE' => 'Y',
                'ISSUING_CENTER' => 'Y',
                'SHIPPING_CENTER' => 'Y',
            ])
            ->setOrder('SORT')
            ->fetchAll();
        return collect($stores)
            ->mapWithKeys(function ($store) {
                return [$store['ID'] => [
                    'address' => $store['ADDRESS'],
                    'phone' => $store['PHONE'],
                    'phoneHref' => 'tel:' . preg_replace('/[^0-9\+]/', '', $store['PHONE']),
                    'schedule' => $store['SCHEDULE'],
                    'description' => $store['DESCRIPTION'],
                ]];
            })
            ->all();
    }

    public function getAllowedStoresWithSizes($offers): array
    {
        $stores = $this->getAllowedStores();

        foreach ($offers as $offer) {
            if (!$offer['id']) continue;
            foreach ($offer['storesAvailability'] as $storeId => $amount) {
                if ($amount > 0 && $stores[$storeId]) {
                    $stores[$storeId]['sizes'][] = $offer['sizeForStore'];
                }
            }
        }

        return $stores;
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
