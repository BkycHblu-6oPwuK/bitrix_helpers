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
use Beeralex\Catalog\Helper\ProductsHelper;
use App\Reviews\ComponentParams;
use App\Reviews\Options;
use App\Reviews\Services\ReviewsService;
use Beeralex\Core\Service\CatalogService;
use Beeralex\Core\Service\IblockService;
use Beeralex\User\User;
use Beeralex\Catalog\Service\ElementService;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

class BeeralexCatalogElement extends \CBitrixComponent implements Controllerable
{
    private \Options $options;
    private ElementService $elementService;

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->elementService = new ElementService(
            service(ProductRepositoryContract::class)
        );
    }

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
        $arResult = $this->elementService->getElementData(
            $this->options->element_id,
            (int)$this->request->get('offerId')
        );

        // url аякс действий
        $arResult['actions'] = $this->getActions();

        if (!$this->options->is_quick_view) {
            $arResult['seo']['sectionPath'] = $this->options->section_path;
        }

        if (Loader::includeModule('beeralex.reviews')) {
            $options = new ComponentParams([
                'PRODUCT_ID' => $arResult['product']['ID'],
                'PAGINATION_LIMIT' => 5,
            ]);
            $service = new ReviewsService($options);
            $arResult['reviews'] = $service->getReviews();
        }

        $arResult['js_data'] = $this->getJsData($arResult);

        return $arResult;
    }

    protected function initSeoData(): void
    {
        /** @var Cmain */
        global $APPLICATION;
        $seoData = $this->arResult['seo'];
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
            'delivery' => $arResult['delivery'] ?? null,
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
            if (empty($this->arResult['product']) || !$this->arResult['product']['ACTIVE']) {
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
        $arResult = $this->elementService->getElementData(
            $this->options->element_id,
            (int)$this->request->get('offerId')
        );
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
