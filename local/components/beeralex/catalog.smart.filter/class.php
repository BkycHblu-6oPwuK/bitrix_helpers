<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\PropertyIndex\Storage as FacetStorage;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Service\SearchService;
use Beeralex\Core\Service\SortingService;
use Beeralex\Core\Service\UrlService;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

CBitrixComponent::includeComponentClass("bitrix:catalog.smart.filter");

class BeeralexCatalogSmartFilter extends CBitrixCatalogSmartFilter
{
    use Cacheable;

    protected ?CatalogService $catalogService = null;
    protected ?SortingService $sortingService = null;

    public function onPrepareComponentParams($arParams)
    {
        is_bool($arParams['USE_FILTER']) || $arParams['USE_FILTER'] = ($arParams['USE_FILTER'] ?? 'Y') === 'Y';
        $this->processServices($arParams);
        return parent::onPrepareComponentParams($arParams);
    }

    public function executeComponent()
    {
        $this->IBLOCK_ID = $this->arParams["IBLOCK_ID"];
        $this->SECTION_ID = $this->arParams["SECTION_ID"];
        $this->FILTER_NAME = $this->arParams["FILTER_NAME"];
        $this->SAFE_FILTER_NAME = htmlspecialcharsbx($this->FILTER_NAME);
        $this->arResult['FILTER_NAME'] = $this->FILTER_NAME;

        if (!$this->arParams['USE_FILTER']) {
            return $this->__includeComponent();
        }

        if (
            $this->arParams["CONVERT_CURRENCY"]
            && $this->arParams["CURRENCY_ID"] != ""
            && Loader::includeModule('currency')
        ) {
            $currencyList = \Bitrix\Currency\CurrencyTable::getList(array(
                'select' => array('CURRENCY'),
                'filter' => array('=CURRENCY' => $this->arParams['CURRENCY_ID'])
            ));
            if ($currency = $currencyList->fetch())
                $this->convertCurrencyId = $currency['CURRENCY'];
            unset($currency);
            unset($currencyList);
        }

        if (static::$iblockIncluded === null)
            static::$iblockIncluded = Loader::includeModule('iblock');
        if (!static::$iblockIncluded)
            return '';

        if (static::$catalogIncluded === null)
            static::$catalogIncluded = Loader::includeModule('catalog');
        if (static::$catalogIncluded) {
            $arCatalog = CCatalogSKU::GetInfoByProductIBlock($this->IBLOCK_ID);
            if (!empty($arCatalog)) {
                $this->SKU_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
                $this->SKU_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
            }
        }

        $this->facet = new \Bitrix\Iblock\PropertyIndex\Facet($this->IBLOCK_ID);

        return $this->__includeComponent();
    }

    public function getCatalogService(): ?CatalogService
    {
        return $this->catalogService;
    }

    public function getSortingService(): ?SortingService
    {
        return $this->sortingService;
    }

    /**
     * @param array $params Параметры компонента
     */
    public static function createInstance(array $params): static
    {
        $instance = new static();
        $instance->arParams = $instance->onPrepareComponentParams($params);
        $instance->initFacet();

        return $instance;
    }

    /**
     * Создает URL для фильтров на основе переданных параметров и кодов свойств.
     * @param array $params Параметры компонента
     */
    public static function makeUrlsForFilters(array $params, array $propertyCodes): array
    {
        global $USER;
        $cacheGroups = ($params['CACHE_GROUPS'] ?? '') !== 'N';
        $instance = static::createInstance($params);
        $cacheSettings = $instance->getCacheSettingsDto(
            time: (int)$instance->arParams['CACHE_TIME'],
            key: $instance->generateCacheId(
                'v1',
                $params['IBLOCK_ID'] ?? 0,
                $params['SECTION_ID'] ?? 0,
                $propertyCodes,
                $params['SEF_RULE'] ?? '',
                $cacheGroups && $USER ? $USER->GetGroups() : false,
            ),
            dir: '/beeralex/catalog_smart_filter_items/',
        );
        $urlService = service(UrlService::class);

        $items = $instance->getCached($cacheSettings, function () use ($instance, $propertyCodes, $cacheSettings, $urlService) {
            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache($cacheSettings->dir);
            $items = $instance->buildFilterData(propertyCodes: $propertyCodes);
            foreach ($items as $pid => $item) {
                if (!in_array($item['CODE'], $propertyCodes, true)) {
                    continue;
                }

                foreach ($item['VALUES'] as $vKey => $valueItem) {
                    if (empty($valueItem['VALUE']) || empty($valueItem['URL_ID'])) {
                        continue;
                    }

                    $instance->arResult['ITEMS'][$pid]['VALUES'][$vKey]['CHECKED'] = true;
                    $url = $instance->makeSmartUrl($instance->arParams['SEF_RULE'], true);
                    $instance->arResult['ITEMS'][$pid]['VALUES'][$vKey]['CHECKED'] = false;

                    $items[$pid]['VALUES'][$vKey]['URL'] = $urlService->cleanUrl($url);
                }
            }
            $instance->setIblockTag();
            $instance->setCurrencyTag();
            $taggedCache->endTagCache();
            return $items;
        });

        return $items;
    }

    /**
     * @param string $url
     * @param bool $apply
     * @param string|false $checkedControlId
     * @return string
     */
    public function makeSmartUrl($url, $apply, $checkedControlId = false)
    {
        $res = parent::makeSmartUrl($url, $apply, $checkedControlId);
        return $this->makeUrl($res);
    }

    public function makeUrl(string $url): string
    {
        if ($this->catalogService !== null) {
            return $this->catalogService->makeUrl($url);
        }
        Loader::requireModule('beeralex.catalog');
        $requestedSortId = $this->sortingService?->getRequestedSortIdOrDefault();
        $query = Context::getCurrent()->getRequest()->get(SearchService::REQUEST_PARAM);

        $uri = new Uri($url);

        if ($this->sortingService && $requestedSortId != $this->sortingService?->getDefaultSortId()) {
            $uri->addParams([SortingService::REQUEST_PARAM => $requestedSortId]);
        }
        if ($query) {
            $uri->addParams([SearchService::REQUEST_PARAM => $query]);
        }

        return $uri->getUri();
    }

    public function getIBlockItems($IBLOCK_ID, array $propertyCodes = []): array
    {
        $allowedIds = [];
        $propertyFilter = [
            '=IBLOCK_ID' => $IBLOCK_ID,
        ];
        if (!empty($propertyCodes)) {
            $propertyFilter['=CODE'] = $propertyCodes;
        }
        $res = PropertyTable::getList([
            'select' => ['ID'],
            'filter' => $propertyFilter,
        ]);
        while ($row = $res->fetch()) {
            $allowedIds[$row['ID']] = true;
        }

        if (empty($allowedIds)) {
            return [];
        }

        $items = [];

        foreach (\CIBlockSectionPropertyLink::GetArray($IBLOCK_ID, $this->SECTION_ID) as $PID => $arLink) {
            if (!empty($propertyCodes) && !isset($allowedIds[$PID])) {
                continue;
            }

            if ($arLink["SMART_FILTER"] !== "Y") {
                continue;
            }

            if ($arLink["ACTIVE"] === "N") {
                continue;
            }

            if ($arLink['FILTER_HINT'] !== null && $arLink['FILTER_HINT'] !== '') {
                $arLink['FILTER_HINT'] = \CTextParser::closeTags($arLink['FILTER_HINT']);
            }

            $rsProperty = \CIBlockProperty::GetByID($PID);
            $arProperty = $rsProperty->Fetch();
            if ($arProperty) {
                $items[$arProperty["ID"]] = [
                    "ID" => $arProperty["ID"],
                    "IBLOCK_ID" => $arProperty["IBLOCK_ID"],
                    "CODE" => $arProperty["CODE"],
                    "~NAME" => $arProperty["NAME"],
                    "NAME" => htmlspecialcharsEx($arProperty["NAME"]),
                    "PROPERTY_TYPE" => $arProperty["PROPERTY_TYPE"],
                    "USER_TYPE" => $arProperty["USER_TYPE"],
                    "USER_TYPE_SETTINGS" => $arProperty["USER_TYPE_SETTINGS"],
                    "DISPLAY_TYPE" => $arLink["DISPLAY_TYPE"],
                    "DISPLAY_EXPANDED" => $arLink["DISPLAY_EXPANDED"],
                    "FILTER_HINT" => $arLink["FILTER_HINT"],
                    "VALUES" => [],
                    "ENCODED_ID" => "",
                ];

                if (
                    $arProperty["PROPERTY_TYPE"] === PropertyTable::TYPE_NUMBER
                    || $arLink["DISPLAY_TYPE"] === SectionPropertyTable::CALENDAR
                ) {
                    $minID = $this->SAFE_FILTER_NAME . '_' . $arProperty['ID'] . '_MIN';
                    $maxID = $this->SAFE_FILTER_NAME . '_' . $arProperty['ID'] . '_MAX';
                    $items[$arProperty["ID"]]["VALUES"] = [
                        "MIN" => ["CONTROL_ID" => $minID, "CONTROL_NAME" => $minID],
                        "MAX" => ["CONTROL_ID" => $maxID, "CONTROL_NAME" => $maxID],
                    ];
                }
            }
        }

        return $items;
    }

    public function getResultItems(array $propertyCodes = []): array
    {
        $items = $this->getIBlockItems($this->IBLOCK_ID, $propertyCodes);
        $this->arResult["PROPERTY_COUNT"] = count($items);
        $this->arResult["PROPERTY_ID_LIST"] = array_keys($items);

        if ($this->SKU_IBLOCK_ID) {
            $this->arResult['SKU_PROPERTY_COUNT'] = 0;
            $this->arResult["SKU_PROPERTY_ID_LIST"] = [$this->SKU_PROPERTY_ID];
            foreach ($this->getIBlockItems($this->SKU_IBLOCK_ID, $propertyCodes) as $PID => $arItem) {
                $items[$PID] = $arItem;
                $this->arResult["SKU_PROPERTY_COUNT"]++;
                $this->arResult["SKU_PROPERTY_ID_LIST"][] = $PID;
            }
        }

        if (!empty($this->arParams["PRICE_CODE"])) {
            foreach ($this->getPriceItems() as $PID => $arItem) {
                $items[$PID] = $arItem;
            }
        }

        return $items;
    }

    public function getPriceItems()
    {
        $items = [];
        if (!empty($this->arParams["PRICE_CODE"])) {
            if (static::$catalogIncluded === null)
                static::$catalogIncluded = Loader::includeModule('catalog');
            if (static::$catalogIncluded) {
                $rsPrice = CCatalogGroup::GetList(
                    ['SORT' => 'ASC', 'ID' => 'ASC'],
                    ['=NAME' => $this->arParams["PRICE_CODE"]],
                    false,
                    false,
                    ['ID', 'NAME', 'NAME_LANG', 'CAN_ACCESS', 'CAN_BUY']
                );
                while ($arPrice = $rsPrice->Fetch()) {
                    if ($arPrice["CAN_ACCESS"] == "Y" || $arPrice["CAN_BUY"] == "Y") {
                        $arPrice["NAME_LANG"] = (string)$arPrice["NAME_LANG"];
                        if ($arPrice["NAME_LANG"] === '')
                            $arPrice["NAME_LANG"] = $arPrice["NAME"];
                        $minID = $this->SAFE_FILTER_NAME . '_P' . $arPrice['ID'] . '_MIN';
                        $maxID = $this->SAFE_FILTER_NAME . '_P' . $arPrice['ID'] . '_MAX';
                        $utf_id = mb_strtolower($arPrice["NAME"]);
                        $items[$arPrice["NAME"]] = [
                            "ID" => $arPrice["ID"],
                            "CODE" => $arPrice["NAME"],
                            "URL_ID" => rawurlencode(str_replace(["/", " "], "-", $utf_id)),
                            "~NAME" => $arPrice["NAME_LANG"],
                            "NAME" => htmlspecialcharsbx($arPrice["NAME_LANG"]),
                            "PRICE" => true,
                            "VALUES" => [
                                "MIN" => [
                                    "CONTROL_ID" => $minID,
                                    "CONTROL_NAME" => $minID,
                                ],
                                "MAX" => [
                                    "CONTROL_ID" => $maxID,
                                ],
                            ],
                            "ENCODED_ID" => md5($arPrice["ID"]),
                        ];
                    }
                }
            }
        }

        return $items;
    }

    public function fillItemValues(&$resultItem, $arProperty, $flag = null)
    {
        if (is_array($arProperty)) {
            if (isset($arProperty["PRICE"])) {
                return null;
            }
            $key = $arProperty["VALUE"];
            $PROPERTY_TYPE = $arProperty["PROPERTY_TYPE"];
            $PROPERTY_USER_TYPE = $arProperty["USER_TYPE"];
            $PROPERTY_ID = $arProperty["ID"];
        } else {
            $key = $arProperty;
            $PROPERTY_TYPE = $resultItem["PROPERTY_TYPE"];
            $PROPERTY_USER_TYPE = $resultItem["USER_TYPE"];
            $PROPERTY_ID = $resultItem["ID"];
            $arProperty = $resultItem;
        }

        if ($PROPERTY_TYPE === PropertyTable::TYPE_FILE) {
            return null;
        } elseif ($PROPERTY_TYPE === PropertyTable::TYPE_NUMBER) {
            $convertKey = (float)$key;
            if ($key == '') {
                return null;
            }

            if (
                !isset($resultItem["VALUES"]["MIN"])
                || !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
                || doubleval($resultItem["VALUES"]["MIN"]["VALUE"]) > $convertKey
            )
                $resultItem["VALUES"]["MIN"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

            if (
                !isset($resultItem["VALUES"]["MAX"])
                || !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
                || doubleval($resultItem["VALUES"]["MAX"]["VALUE"]) < $convertKey
            )
                $resultItem["VALUES"]["MAX"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

            return null;
        } elseif ($arProperty["DISPLAY_TYPE"] === SectionPropertyTable::CALENDAR) {
            $date = mb_substr($key, 0, 10);
            if (!$date) {
                return null;
            }
            $timestamp = MakeTimeStamp($date, "YYYY-MM-DD");
            if (!$timestamp) {
                return null;
            }

            if (
                !isset($resultItem["VALUES"]["MIN"])
                || !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
                || $resultItem["VALUES"]["MIN"]["VALUE"] > $timestamp
            )
                $resultItem["VALUES"]["MIN"]["VALUE"] = $timestamp;

            if (
                !isset($resultItem["VALUES"]["MAX"])
                || !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
                || $resultItem["VALUES"]["MAX"]["VALUE"] < $timestamp
            )
                $resultItem["VALUES"]["MAX"]["VALUE"] = $timestamp;

            return null;
        } elseif ($PROPERTY_TYPE === PropertyTable::TYPE_ELEMENT && $key <= 0) {
            return null;
        } elseif ($PROPERTY_TYPE === PropertyTable::TYPE_SECTION && $key <= 0) {
            return null;
        } elseif ($key == '') {
            return null;
        }

        $arUserType = [];
        if ($PROPERTY_USER_TYPE != "") {
            $arUserType = CIBlockProperty::GetUserType($PROPERTY_USER_TYPE);
            if (isset($arUserType["GetExtendedValue"]))
                $PROPERTY_TYPE = "Ux";
            elseif (isset($arUserType["GetPublicViewHTML"]))
                $PROPERTY_TYPE = "U";
        }

        if ($PROPERTY_USER_TYPE === \CIBlockPropertyDateTime::USER_TYPE) {
            $key = call_user_func_array(
                $arUserType["GetPublicViewHTML"],
                [
                    $arProperty,
                    ["VALUE" => $key],
                    ["MODE" => "SIMPLE_TEXT", "DATETIME_FORMAT" => "SHORT"],
                ]
            );
            $PROPERTY_TYPE = PropertyTable::TYPE_STRING;
        }

        $htmlKey = htmlspecialcharsbx($key);
        if (isset($resultItem["VALUES"][$htmlKey])) {
            return $htmlKey;
        }

        $file_id = null;
        $url_id = null;

        switch ($PROPERTY_TYPE) {
            case PropertyTable::TYPE_LIST:
                $enum = CIBlockPropertyEnum::GetByID($key);
                if ($enum) {
                    $value = $enum["VALUE"];
                    $sort  = $enum["SORT"];
                    $url_id = mb_strtolower($enum["XML_ID"]);
                } else {
                    return null;
                }
                break;
            case PropertyTable::TYPE_ELEMENT:
                if (!isset($this->cache[$PROPERTY_TYPE][$key])) {
                    $this->predictIBElementFetch([$key]);
                }

                if (!$this->cache[$PROPERTY_TYPE][$key])
                    return null;

                $value = $this->cache[$PROPERTY_TYPE][$key]["NAME"];
                $sort = $this->cache[$PROPERTY_TYPE][$key]["SORT"];
                if ($this->cache[$PROPERTY_TYPE][$key]["CODE"])
                    $url_id = mb_strtolower($this->cache[$PROPERTY_TYPE][$key]["CODE"]);
                else
                    $url_id = mb_strtolower($value);
                break;
            case PropertyTable::TYPE_SECTION:
                if (!isset($this->cache[$PROPERTY_TYPE][$key])) {
                    $this->predictIBSectionFetch([$key]);
                }

                if (!$this->cache[$PROPERTY_TYPE][$key])
                    return null;

                $value = $this->cache[$PROPERTY_TYPE][$key]['DEPTH_NAME'];
                $sort = $this->cache[$PROPERTY_TYPE][$key]["LEFT_MARGIN"];
                if ($this->cache[$PROPERTY_TYPE][$key]["CODE"])
                    $url_id = mb_strtolower($this->cache[$PROPERTY_TYPE][$key]["CODE"]);
                else
                    $url_id = mb_strtolower($value);
                break;
            case "U":
                if (!isset($this->cache[$PROPERTY_ID]))
                    $this->cache[$PROPERTY_ID] = [];

                if (!isset($this->cache[$PROPERTY_ID][$key])) {
                    $this->cache[$PROPERTY_ID][$key] = call_user_func_array(
                        $arUserType["GetPublicViewHTML"],
                        [
                            $arProperty,
                            ["VALUE" => $key],
                            ["MODE" => "SIMPLE_TEXT"],
                        ]
                    );
                }

                $value = $this->cache[$PROPERTY_ID][$key];
                $sort = 0;
                $url_id = mb_strtolower($value);
                break;
            case "Ux":
                if (!isset($this->cache[$PROPERTY_ID]))
                    $this->cache[$PROPERTY_ID] = [];

                if (!isset($this->cache[$PROPERTY_ID][$key])) {
                    $this->cache[$PROPERTY_ID][$key] = call_user_func_array(
                        $arUserType["GetExtendedValue"],
                        [
                            $arProperty,
                            ["VALUE" => $key],
                        ]
                    );
                }

                if ($this->cache[$PROPERTY_ID][$key]) {
                    $value = $this->cache[$PROPERTY_ID][$key]['VALUE'];
                    $file_id = $this->cache[$PROPERTY_ID][$key]['FILE_ID'];
                    $sort = ($this->cache[$PROPERTY_ID][$key]['SORT'] ?? 0);
                    $url_id = mb_strtolower($this->cache[$PROPERTY_ID][$key]['UF_XML_ID']);
                } else {
                    return null;
                }
                break;
            default:
                $value = $key;
                $sort = 0;
                $url_id = mb_strtolower($value);
                break;
        }

        $keyCrc = abs(crc32($htmlKey));
        $safeValue = htmlspecialcharsex($value);
        $sort = (int)$sort;

        $filterPropertyID = $this->SAFE_FILTER_NAME . '_' . $PROPERTY_ID;
        $filterPropertyIDKey = $filterPropertyID . '_' . $keyCrc;
        $resultItem["VALUES"][$htmlKey] = [
            "CONTROL_ID" => $filterPropertyIDKey,
            "CONTROL_NAME" => $filterPropertyIDKey,
            "CONTROL_NAME_ALT" => $filterPropertyID,
            "HTML_VALUE_ALT" => $keyCrc,
            "HTML_VALUE" => "Y",
            "VALUE" => $safeValue,
            "SORT" => $sort,
            "UPPER" => mb_strtoupper($safeValue),
            "FLAG" => $flag,
        ];

        if ($file_id) {
            $resultItem["VALUES"][$htmlKey]['FILE'] = CFile::GetFileArray($file_id);
        }

        if ($url_id <> '') {
            $resultItem["VALUES"][$htmlKey]['URL_ID'] = rawurlencode(str_replace(["/", " "], "-", $url_id));
        }

        return $htmlKey;
    }

    /**
     * метод построения данных фильтра
     *
     * @param array $facetFilter Доп. условия для facet->query() (ACTIVE, ACTIVE_DATE и т.п.)
     */
    public function buildFilterData(array $facetFilter = ['ACTIVE_DATE' => 'Y', 'ACTIVE' => 'Y'], array $propertyCodes = []): array
    {
        $this->arResult['ITEMS'] = $this->getResultItems($propertyCodes);

        if ($this->facet->isValid() && !empty($this->arResult['ITEMS'])) {
            $this->facet->setSectionId($this->SECTION_ID);

            $tmpProperty  = [];
            $dictionaryId = [];

            $res = $this->facet->query($facetFilter);
            while ($rowData = $res->fetch()) {
                $facetId = $rowData['FACET_ID'];
                if (!FacetStorage::isPropertyId($facetId)) {
                    continue;
                }
                $pid = FacetStorage::facetIdToPropertyId($facetId);
                if (!isset($this->arResult['ITEMS'][$pid])) {
                    continue;
                }
                $rowData['PID'] = $pid;
                $tmpProperty[]  = $rowData;
                if ($this->arResult['ITEMS'][$pid]['PROPERTY_TYPE'] === PropertyTable::TYPE_STRING) {
                    $dictionaryId[] = $rowData['VALUE'];
                }
            }

            $this->processProperties($this->arResult, $tmpProperty, $dictionaryId);
        }

        return $this->arResult['ITEMS'];
    }


    protected function initFacet(): static
    {
        $this->IBLOCK_ID        = (int)$this->arParams['IBLOCK_ID'];
        $this->SECTION_ID       = (int)$this->arParams['SECTION_ID'];
        $this->FILTER_NAME      = $this->arParams['FILTER_NAME'];
        $this->SAFE_FILTER_NAME = htmlspecialcharsbx($this->FILTER_NAME);
        $this->arResult         = ['FILTER_NAME' => $this->FILTER_NAME];

        if (
            $this->arParams['CONVERT_CURRENCY']
            && $this->arParams['CURRENCY_ID'] != ''
            && Loader::includeModule('currency')
        ) {
            $currencyList = \Bitrix\Currency\CurrencyTable::getList([
                'select' => ['CURRENCY'],
                'filter' => ['=CURRENCY' => $this->arParams['CURRENCY_ID']],
            ]);
            if ($currency = $currencyList->fetch()) {
                $this->convertCurrencyId = $currency['CURRENCY'];
            }
            unset($currency, $currencyList);
        }

        if (!Loader::includeModule('iblock')) {
            throw new \RuntimeException('Модуль iblock недоступен');
        }

        if (Loader::includeModule('catalog')) {
            $arCatalog = \CCatalogSKU::GetInfoByProductIBlock($this->IBLOCK_ID);
            if (!empty($arCatalog)) {
                $this->SKU_IBLOCK_ID   = $arCatalog['IBLOCK_ID'];
                $this->SKU_PROPERTY_ID = $arCatalog['SKU_PROPERTY_ID'];
            }
        }

        $this->facet = new \Bitrix\Iblock\PropertyIndex\Facet($this->IBLOCK_ID);

        return $this;
    }

    protected function processServices(array &$arParams): void
    {
        Loader::requireModule('beeralex.catalog');
        $catalogService = $arParams['CATALOG_SERVICE'] ?? null;
        $sortingService = $arParams['SORTING_SERVICE'] ?? null;
        if ($catalogService === null || !($catalogService instanceof CatalogService)) {
            $catalogService = service(CatalogService::class);
        }

        if ($sortingService === null || !($sortingService instanceof SortingService)) {
            $sortingService = null;
        }
        $this->catalogService = $catalogService;
        $this->sortingService = $sortingService;
        unset($arParams['CATALOG_SERVICE'], $arParams['SORTING_SERVICE']);
    }
}
