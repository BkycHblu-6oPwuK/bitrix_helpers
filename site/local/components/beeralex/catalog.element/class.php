<?

class BeeralexCatalogElement extends \CBitrixComponent
{
    public function __construct($component = null)
    {
        parent::__construct($component);
    }

    public function executeComponent()
    {
        if ($this->startResultCache(false, [$this->request->get('offerId')], 'beeralex/catalog.element')) {
            
            $this->includeComponentTemplate();
        }
        $this->initSeoData();

        return $this->arResult;
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
}
