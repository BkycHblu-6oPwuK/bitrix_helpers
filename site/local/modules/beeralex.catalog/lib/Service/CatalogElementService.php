<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Repository\OffersRepository;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

class CatalogElementService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productRepository,
        protected readonly OffersRepository $offersRepository
    ) {
        $this->productRepository = $productRepository;
        Loader::includeModule('iblock');
    }

    public function getElementData(int $elementId, ?int $offerId = null): array
    {
        $product = $this->productRepository->one(['ID' => $elementId]);
        if (!$product) {
            return [];
        }

        $offers = service(\Beeralex\Catalog\Contracts\OfferRepositoryContract::class)->getOffersByProductIds([$elementId]);
        $product['OFFERS'] = $offers[$elementId] ?? [];

        // Handle selected offer
        if ($offerId) {
            $offers = collect($product['OFFERS']);
            $offer = $offers->first(fn($value) => $value['ID'] == $offerId);
            if ($offer) {
                $product['PRESELECTED_OFFER'] = $offer;
                $product['SELECTED_OFFER_ID'] = $offer['ID'];
            }
        }

        $properties = $this->getProperties($product['IBLOCK_ID'], $elementId);

        $data = [
            'product' => $product,
            'propertiesDefault' => $properties,
            'properties' => $this->getFormattedProperties($properties),
            'seo' => $this->getSeoData($product['IBLOCK_ID'], $elementId),
        ];

        $data['colors'] = $this->getColors(
            $product['ID'],
            $product['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? null,
            $properties['TSVET_NA_SAYTE']['TABLE_NAME'] ?? null
        );

        return $data;
    }

    protected function getProperties(int $iblockId, int $elementId): array
    {
        $properties = [];
        $res = \CIBlockElement::GetProperty($iblockId, $elementId, '', '', ['=ACTIVE' => 'Y']);
        while ($property = $res->GetNext()) {
            $property = \CIBlockFormatProperties::GetDisplayValue([], $property);
            if (!empty($property['VALUE_ENUM'])) {
                $property['VALUE'] = $property['VALUE_ENUM'];
            } else {
                $property['VALUE'] = $property['DISPLAY_VALUE'];
            }
            if (!empty($property['VALUE'])) {
                $value = [
                    'NAME' => $property['NAME'],
                    'CODE' => $property['CODE'],
                    'VALUE' => mb_ucfirst($property['VALUE']),
                ];
                if (is_array($property['USER_TYPE_SETTINGS'])) {
                    $value['XML_ID'] = $property['~VALUE'];
                    $value['TABLE_NAME'] = $property['USER_TYPE_SETTINGS']['TABLE_NAME'];
                }
                if ($property['CODE'] == 'TSVET_NA_SAYTE') {
                    $entity = $this->getHighload($value['TABLE_NAME']);
                    $result = $entity::query()->setSelect(['UF_FILE'])->where('UF_XML_ID', $property['~VALUE'])->fetch();
                    $value['FILE'] = $result ? \CFile::GetPath($result['UF_FILE']) : null;
                }
                if ($property['MULTIPLE'] == 'Y') {
                    $properties[$property['CODE']][] = $value;
                } else {
                    $properties[$property['CODE']] = $value;
                }
            }
        }
        return $properties;
    }

    protected function getFormattedProperties(array $properties): array
    {
        $propertyKeys = [
            'KARMANY',
            'SILUET',
            'TSVET_NA_SAYTE',
            'OTDELKA',
            'DLINA_IZDELIYA',
            'SOSTAV'
        ];
        return array_values(array_filter(array_map(function ($key) use ($properties) {
            if (!empty($properties[$key]['VALUE'])) {
                return [
                    'NAME' => $properties[$key]['NAME'],
                    'VALUE' => $properties[$key]['VALUE']
                ];
            }
            return null;
        }, $propertyKeys)));
    }

    protected function getColors(int $productId, ?string $article, ?string $hlTableName): array
    {
        if (!$article || !$hlTableName) return [];
        $highload = $this->getHighload($hlTableName);
        if (!$highload) return [];

        $colors = [];
        $elements = $this->productRepository->all(
            ['=PROPERTY.CML2_ARTICLE' => $article, '=ACTIVE' => 'Y', '=CATALOG.AVAILABLE' => 'Y'],
            ['ID', 'TSVET_ID' => 'PROPERTY.TSVET_NA_SAYTE']
        );

        foreach ($elements as $element) {
            $hlelement = $highload::query()->setSelect(['UF_FILE'])->where('UF_XML_ID', $element['TSVET_ID'])->fetch();
            $element['FILE'] = $hlelement ? \CFile::GetPath($hlelement['UF_FILE']) : null;
            if ($element['FILE']) {
                $colors[] = [
                    'id' => $element['ID'],
                    'file' => $element['FILE'],
                ];
            }
        }

        usort($colors, static function ($a, $b) use ($productId) {
            return $a['id'] === $productId ? -1 : ($b['id'] === $productId ? 1 : 0);
        });
        return $colors;
    }

    protected function getSeoData(int $iblockId, int $elementId): array
    {
        return (new ElementValues($iblockId, $elementId))->getValues();
    }

    protected function getHighload(?string $tableName): ?string
    {
        if (Loader::IncludeModule('highloadblock') && $tableName) {
            static $entity = [];
            if (empty($entity[$tableName])) {
                $hlblock = HighloadBlockTable::getList(['filter' => ['=TABLE_NAME' => $tableName]])->fetch();
                if ($hlblock) {
                    $entityClass = HighloadBlockTable::compileEntity($hlblock)->getDataClass();
                    $entity[$tableName] = $entityClass;
                }
            }
            return $entity[$tableName] ?? null;
        }
        return null;
    }
}
