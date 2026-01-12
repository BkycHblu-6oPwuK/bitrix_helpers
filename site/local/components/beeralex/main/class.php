<?php

use Beeralex\Api\Domain\Iblock\Content\MainService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexMain extends \CBitrixComponent
{
    protected readonly MainService $mainService;
    
    public function onPrepareComponentParams($params)
    {
        $this->mainService = service(MainService::class);
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
        return $this->mainService->getContent();
    }
}
