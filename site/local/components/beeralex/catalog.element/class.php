<?
require_once __DIR__ . '/Options.php';

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use App\Catalog\Helper\ProductsHelper;
use Beeralex\Core\Helpers\DateHelper;
use App\Reviews\ComponentParams;
use App\Reviews\Options;
use App\Reviews\Services\ReviewsService;
use Beeralex\Core\Helpers\CatalogHelper;
use Beeralex\Core\Helpers\IblockHelper;
use Beeralex\User\User;

class BeeralexCatalogElement extends \CBitrixComponent implements Controllerable
{
    private \Options $options;

    public function onPrepareComponentParams($params)
    {
        $customSignedParameters = $this->request->get('customSignedParameters');
        $paramsIsEmpty = false;
        if ($customSignedParameters) {
            $params = unserialize(base64_decode($customSignedParameters), ['allowed_classes' => false]);
        }
        if (empty($params)) {
            $params = [];
            $paramsIsEmpty = true;
        }
        $action = $this->request->get('action');
        if ($action) {
            $params['IS_QUICK_VIEW'] = $action === 'quickView';
        }

        $selectedProduct = $this->request->get('selectedProduct');
        if ($selectedProduct) {
            $params['ELEMENT_ID'] = $selectedProduct;
        }
        if ($paramsIsEmpty && $params['ELEMENT_ID']) {
            $this->setParamsByElementId($params['ELEMENT_ID'], $params);
        }

        $this->options = new \Options($params);
        return $params;
    }

    protected function setParamsByElementId(int $elementId, array &$params)
    {
        $element = ElementTable::query()->setSelect([
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'CODE',
            'SECTION_URL' => 'IBLOCK.SECTION_PAGE_URL',
            'DETAIL_URL' => 'IBLOCK.DETAIL_PAGE_URL',
            'IBLOCK_TYPE' => 'IBLOCK.IBLOCK_TYPE_ID'
        ])
            ->where('ID', $elementId)
            ->setCacheTtl(360000)
            ->cacheJoins(true)
            ->exec()
            ->fetch();
        if ($element) {
            $params['IBLOCK_ID'] = $element['IBLOCK_ID'];
            $params['IBLOCK_TYPE'] = $element['IBLOCK_TYPE'];
            $params['SECTION_ID'] = $element['IBLOCK_SECTION_ID'];
            $params['ELEMENT_CODE'] = $element['CODE'];
            $params['SECTION_URL'] = $element['SECTION_URL'];
            $params['DETAIL_URL'] = $element['DETAIL_URL'];
        }
    }

    public function configureActions()
    {
        return [
            'changeColor' => [
                'prefilters' => [
                    new Csrf()
                ],
            ],
            'quickView' => [
                'prefilters' => [],
            ],
        ];
    }

    protected function listKeysSignedParameters()
    {
        return [
            'SECTION_ID',
            'SECTION_CODE',
            'IBLOCK_ID',
        ];
    }

    public function executeComponent()
    {
        if (!$this->options->is_quick_view) {
            if ($this->startResultCache(false, [$this->request->get('offerId'), User::current()->isAuthorized()], 'beeralex/catalog.element')) {
                $this->arResult = $this->getResult();
                $this->handleResult();
                $this->includeComponentTemplate();
            }
            $this->initSeoData();
        } else {
            $this->arResult = $this->getResult();
            $this->includeComponentTemplate();
        }

        return $this->options->element_id ? [
            'elementId' => $this->options->element_id,
            'sectionId' => $this->options->section_id
        ] : null;
    }

    protected function getResult(): array
    {
        $arResult = $this->getDefaultData();
        $arResult['colors'] = $this->getColors($arResult['product']['id'], $arResult['product']['article'], $arResult['propertiesDefault']['TSVET_NA_SAYTE']['TABLE_NAME']);
        // url аякс действий
        $arResult['actions'] = $this->getActions();
        $arResult['delivery']['sdekDate'] = DateHelper::getDateFormatted((new DateTime())->add('+7 days')) . ' г., с 10 до 20';

        if (!$this->options->is_quick_view) {
            // seo
            $arResult['seo'] = $this->getSeoData();
            $arResult['seo']['sectionPath'] = $this->options->section_path;
        }

        $arResult['js_data'] = $this->getJsData($arResult);

        return $arResult;
    }

    protected function getDefaultData(): array
    {
        // Получение информации о товаре и его предложениях
        $arResult['product'] = $this->getProductData($this->options->element_id);
        // Обработка выбранного торгового предложения
        $arResult = $this->handleSelectedOffer((int)$this->request->get('offerId'), $arResult);

        // Формирование свойств товара
        $arResult['propertiesDefault'] = $this->getProperties();
        $arResult['properties'] = $this->getFormattedProperties($arResult['propertiesDefault']);
        $arResult['reviews'] = null;
        if (Loader::includeModule('beeralex.reviews')) {
            $options = new ComponentParams([
                'PRODUCT_ID' => $arResult['product']['id'],
                'PAGINATION_LIMIT' => 5,
            ]);
            $service = new ReviewsService($options);
            $arResult['reviews'] = $service->getReviews();
        }
        return $arResult;
    }

    protected function getProductData(int $elementId): array
    {
        $productData = ProductsHelper::getProductsAndOffers([$elementId])[$elementId];
        // if (empty($productData['offers'])) {
        //     $productData = ProductsHelper::getProductsAndOffers([$elementId], false)[$elementId];
        //     $productData['availableMode'] = false;
        // } else {
        //     $productData['availableMode'] = true;
        // }
        return $productData ?? [];
    }

    protected function handleSelectedOffer(?int $offerId, array $arResult): array
    {
        $offers = collect($arResult['product']['offers']);
        $offer = $offers->first(fn($value) => $value['id'] == $offerId);
        if ($offer) {
            $arResult['product']['preselectedOffer'] = $offer;
            $arResult['product']['selectedOfferId'] = $offer['id'];
        }
        return $arResult;
    }

    protected function getProperties(): array
    {
        $properties = [];
        $res = \CIBlockElement::GetProperty($this->options->iblock_id, $this->options->element_id, '', '', ['=ACTIVE' => 'Y']);
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
                    $value['FILE'] = $result ? CFile::GetPath($result['UF_FILE']) : null;
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

    /**
     * @return null|\Bitrix\Main\ORM\Data\DataManager|string
     */
    protected function getHighload(?string $tableName): ?string
    {
        if (Loader::IncludeModule('highloadblock') && $tableName) {
            static $entity = [];
            if (!$entity[$tableName]) {
                $hlblock = HighloadBlockTable::getList([
                    'filter' => [
                        '=TABLE_NAME' => $tableName
                    ],
                ])->fetch();
                if ($hlblock) {
                    $entity[$tableName] = HighloadBlockTable::compileEntity($hlblock)->getDataClass();
                }
            }
            return $entity[$tableName];
        }
        return null;
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
                    'name' => $properties[$key]['NAME'],
                    'value' => $properties[$key]['VALUE'],
                    'code' => $key
                ];
            }
        }, $propertyKeys)));
    }

    protected function getColors(int $productId, ?string $acticle, ?string $hlTableName): array
    {
        if (!$acticle || !$hlTableName) return [];
        $highload = $this->getHighload($hlTableName);
        if (!$highload) return [];
        $colors = [];
        $elements = CatalogHelper::addCatalogToQuery(IblockHelper::getElementApiTableByCode('catalog')::query())->setSelect(['ID', 'TSVET_ID' => 'TSVET_NA_SAYTE.VALUE'])
            //->where('CML2_ARTICLE.VALUE', $acticle)
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->exec();
        while ($element = $elements->fetch()) {
            $hlelement = $highload::query()->setSelect(['UF_FILE'])->where('UF_XML_ID', $element['TSVET_ID'])->fetch();
            $element['FILE'] = $hlelement ? CFile::GetPath($hlelement['UF_FILE']) : null;
            if ($element['FILE']) {
                $colors[] = [
                    'id' => (int)$element['ID'],
                    'file' => $element['FILE']
                ];
            }
        }
        usort($colors, static function ($a, $b) use ($productId) {
            return $a['id'] === $productId ? -1 : ($b['id'] === $productId ? 1 : 0);
        });
        return $colors;
    }

    protected function getSeoData(): array
    {
        return (new ElementValues($this->options->iblock_id, $this->options->element_id))->getValues();
    }

    protected function initSeoData(): void
    {
        /** @var Cmain */
        global $APPLICATION;
        $seoData = $this->arResult['seo'] ?? $this->getSeoData();
        $APPLICATION->SetTitle($seoData['ELEMENT_META_TITLE']);
        $APPLICATION->SetPageProperty('description', $seoData['ELEMENT_META_DESCRIPTION']);
        $APPLICATION->SetPageProperty('keywords', $seoData['ELEMENT_META_KEYWORDS']);
        $APPLICATION->SetPageProperty('title', $seoData['ELEMENT_META_TITLE']);
    }

    protected function getJsData(array $arResult): mixed
    {
        $data = [
            'product' => $arResult['product'],
            'siteId' => SITE_ID,
            'propertiesDefault' => $arResult['propertiesDefault'],
            'properties' => $arResult['properties'],
            'colors' => $arResult['colors'],
            'actions' => $arResult['actions'],
            'delivery' => $arResult['delivery'],
            'reviews' => $arResult['reviews'],
            'signedParameters' => $this->getSignedParameters(),
        ];
        return Json::encode($data);
    }

    protected function getActions(): array
    {
        return [
            'changeColor' => \Bitrix\Main\Engine\UrlManager::getInstance()->createByBitrixComponent($this, 'changeColor'),
        ];
    }

    protected function handleResult(): void
    {
        try {
            if (!$this->arResult['product']['active']) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $this->set404();
        }
    }

    protected function set404(): void
    {
        $this->abortResultCache();
        \Bitrix\Iblock\Component\Tools::process404(
            'Страница не найдена',
            true,
            true,
            true
        );
    }

    public function changeColorAction(): array
    {
        $arResult = $this->getDefaultData();
        return [
            'product' => $arResult['product'],
            'propertiesDefault' => $arResult['propertiesDefault'],
            'properties' => $arResult['properties'],
            'reviews' => $arResult['reviews'],
        ];
    }

    public function quickViewAction()
    {
        global $APPLICATION;
        $result = '';
        ob_start();
        $APPLICATION->IncludeComponent(
            'beeralex:catalog.element',
            'quick_view',
            [
                'IBLOCK_TYPE' => $this->options->iblock_type,
                'IBLOCK_ID' => $this->options->iblock_id,
                'ELEMENT_ID' => $this->options->element_id,
                'ELEMENT_CODE' => $this->options->element_code,
                'SECTION_ID' => $this->options->section_id,
                'SECTION_CODE' => $this->options->section_code,
                'SECTION_URL' => $this->options->section_url,
                'DETAIL_URL' => $this->options->detail_url,
                'IS_QUICK_VIEW' => true
            ]
        );
        $result = ob_get_contents();
        ob_get_clean();
        return $result;
    }
}
