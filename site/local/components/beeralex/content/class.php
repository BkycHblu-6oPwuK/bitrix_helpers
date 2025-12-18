<?php

use Beeralex\Api\Domain\Iblock\Content\ContentService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexContent extends \CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        if (!$params['CODE']) {
            throw new \RuntimeException("CODE value in arParams is required");
        }
        if($params['CONTENT_SERVICE'] === null || !($params['CONTENT_SERVICE'] instanceof ContentService)) {
            $params['CONTENT_SERVICE'] = service(ContentService::class);
        }
        return $params;
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult = $this->getContent();
            $this->endResultCache();
        }
        $this->includeComponentTemplate();
    }

    protected function getContent(): array
    {
        return $this->arParams['CONTENT_SERVICE']->getContentByCode($this->arParams['CODE']);
    }
}
