<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Repository\PropertyFeaturesRepository;
use Beeralex\Core\Repository\PropertyRepository;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\HlblockService;
use Bitrix\Main\Loader;

class CatalogElementService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productRepository,
        protected readonly OfferRepositoryContract $offersRepository,
        protected readonly PropertyRepository $propertyRepository,
        protected readonly PropertyFeaturesRepository $propertyFeaturesRepository,
        protected readonly HlblockService $hlblockService,
        protected readonly FileService $fileService,
    ) {
        Loader::includeModule('iblock');
    }

    public function getElementData(int $elementId, ?int $offerId = null): array
    {
        $products = $this->productRepository->getProducts([$elementId]);
        $product = $products[array_key_first($products)] ?? null;
        if (!$product) {
            return [];
        }
        $offers = $this->offersRepository->getOffersByProductIds([$elementId]);

        $product['OFFERS'] = $offers[$elementId] ?? [];
        $propertiesIds = $this->getPropertiesIds($product);
        $properties = $this->propertyRepository->getByIds($propertiesIds, ['ID', 'CODE', 'NAME', 'USER_TYPE_SETTINGS_LIST', 'USER_TYPE', 'PROPERTY_TYPE']);
        $propertyTreeFeatures = $this->propertyFeaturesRepository->getByOffersTree(['PROPERTY_ID', 'IS_ENABLED']);
        $tableNames = $this->getTableNames($properties);
        $highloadClasses = $this->hlblockService->getHlBlocksByTableNames($tableNames);
        $this->buildPropertiesForProduct($product, $properties, $propertyTreeFeatures, $highloadClasses);
        $product['OFFER_TREE'] = $this->buildOfferTree($product['OFFERS']);
        $preselectedOffer = $this->getPreselectedOffer($product, $offerId);
        $product['PRESELECTED_OFFER'] = $preselectedOffer;
        $product['SELECTED_OFFER_ID'] = $preselectedOffer['ID'] ?? null;
        return $product;
    }

    protected function getPreselectedOffer(array $product, ?int $offerId): array
    {
        foreach ($product['OFFERS'] as $offer) {
            if($offerId === null) {
                return $offer;
            }
            if ((int)$offer['ID'] === $offerId) {
                return $offer;
            }
        }

        return [];
    }

    protected function getPropertiesIds(array $product): array
    {
        $propertyIds = [];

        if (!empty($product['IBLOCK_PROPERTY_ID'])) {
            $propertyIds[] = $product['IBLOCK_PROPERTY_ID'];
        }

        foreach ($product as $item) {
            if (is_array($item)) {
                foreach ($item as $key => $subItem) {
                    if ($key === 'IBLOCK_PROPERTY_ID' && !empty($subItem)) {
                        $propertyIds[$subItem] = $subItem;
                    } elseif (is_array($subItem) && !empty($subItem['IBLOCK_PROPERTY_ID'])) {
                        $propertyIds[$subItem['IBLOCK_PROPERTY_ID']] = $subItem['IBLOCK_PROPERTY_ID'];
                    }
                }
            }
        }

        if (!empty($product['OFFERS']) && is_array($product['OFFERS'])) {
            foreach ($product['OFFERS'] as $offer) {
                foreach ($offer as $item) {
                    if (is_array($item) && !empty($item['IBLOCK_PROPERTY_ID'])) {
                        $propertyIds[$item['IBLOCK_PROPERTY_ID']] = $item['IBLOCK_PROPERTY_ID'];
                    }
                }
            }
        }

        return array_values($propertyIds);
    }

    protected function getTableNames(array $properties): array
    {
        $tableNames = [];
        foreach ($properties as $property) {
            if (!empty($property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'])) {
                $tableNames[$property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']] = $property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'];
            }
        }

        return array_values($tableNames);
    }

    protected function buildPropertiesForProduct(array &$product, array $properties, array $propertyTreeFeatures, array $highloadClasses): void
    {
        $getFromHighload = function ($tableEntity, $value) {
            $result = null;
            if ($tableEntity && $value) {
                $rsData = $this->fileService->addPictireSrcInQuery($tableEntity::query(), 'UF_FILE')->setSelect(['*', 'PICTURE_SRC'])->setFilter(['=UF_XML_ID' => $value])->setLimit(1)->exec();
                if ($arData = $rsData->fetch()) {
                    $result = $arData;
                }
            }
            return $result;
        };
        
        foreach ($properties as &$property) {
            // property features
            $property['HLBLOCK_CLASS'] = null;
            $property['TREE'] = false;
            foreach ($propertyTreeFeatures as $feat) {
                if (!empty($feat['PROPERTY_ID']) && $feat['PROPERTY_ID'] == $property['ID']) {
                    $property['TREE'] = $feat['IS_ENABLED'] === 'Y';
                    break;
                }
            }
            if (
                !empty($property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'])
                && isset($highloadClasses[$property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']])
            ) {
                $property['HLBLOCK_CLASS'] = $highloadClasses[$property['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']];
            }
            if (isset($product[$property['CODE']])) {
                if (is_array($product[$property['CODE']][0])) {
                    foreach ($product[$property['CODE']] as &$propValue) {
                        $propValue = array_merge($propValue, $property);
                        $propValue['HL_DATA'] = $getFromHighload($propValue['HLBLOCK_CLASS'], $propValue['VALUE']);
                    }
                } else {
                    $product[$property['CODE']] = array_merge($product[$property['CODE']], $property);
                    $product[$property['CODE']]['HL_DATA'] = $getFromHighload($product[$property['CODE']]['HLBLOCK_CLASS'], $product[$property['CODE']]['VALUE']);
                }
            }
            foreach ($product['OFFERS'] as &$offer) {
                if (isset($offer[$property['CODE']])) {
                    if (is_array($offer[$property['CODE']][0])) {
                        foreach ($offer[$property['CODE']] as &$propValue) {
                            $propValue = array_merge($propValue, $property);
                            $propValue['HL_DATA'] = $getFromHighload($propValue['HLBLOCK_CLASS'], $propValue['VALUE']);
                        }
                    } else {
                        $offer[$property['CODE']] = array_merge($offer[$property['CODE']], $property);
                        $offer[$property['CODE']]['HL_DATA'] = $getFromHighload($offer[$property['CODE']]['HLBLOCK_CLASS'], $offer[$property['CODE']]['VALUE']);
                    }
                }
            }
        }

    }

    protected function buildOfferTree(array $offers): array
    {
        $treeProps = [];
        $offersMap = [];

        if (empty($offers)) {
            return ['PROPS' => [], 'MAP' => []];
        }

        foreach ($offers as $offer) {
            foreach ($offer as $code => $prop) {
                if (is_array($prop) && !empty($prop['TREE'])) {
                    $treeProps[$code] = [
                        'CODE' => $code,
                        'NAME' => $prop['NAME'] ?? $code,
                        'VALUES' => []
                    ];
                }
            }
        }

        if (empty($treeProps)) {
            return ['PROPS' => [], 'MAP' => []];
        }

        foreach ($offers as $offer) {
            $offerId = $offer['ID'];
            $offersMap[$offerId] = [];

            foreach ($treeProps as $code => &$treeProp) {
                if (!isset($offer[$code]['VALUE'])) continue;
                $prop = $offer[$code];
                $value = $prop['ITEM']['VALUE'] ?? $prop['VALUE'];
                $hl = $prop['HL_DATA'] ?? null;

                $offersMap[$offerId][$code] = $value;

                if (!isset($treeProp['VALUES'][$value])) {
                    $treeProp['VALUES'][$value] = [
                        'ID' => $prop['IBLOCK_PROPERTY_ID'] ?? 0,
                        'VALUE' => $value,
                        'NAME' => $hl['UF_NAME'] ?? $prop['NAME'] ?? '',
                        'XML_ID' => $prop['XML_ID'] ?? null,
                        'PICTURE_SRC' => $hl['PICTURE_SRC'] ?? null,
                    ];
                }
            }
        }

        // Убираем ключи
        foreach ($treeProps as &$prop) {
            $prop['VALUES'] = array_values($prop['VALUES']);
        }

        return [
            'PROPS' => array_values($treeProps),
            'MAP' => $offersMap
        ];
    }
}
