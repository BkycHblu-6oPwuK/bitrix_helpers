<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.section");

class BeeralexCatalogSection extends \CatalogSectionComponent
{
    public function executeComponent()
    {
        if($this->arParams['API_MODE']) {
            ob_start();
        }

        parent::executeComponent();

        if($this->arParams['API_MODE']) {
            ob_get_clean();
        }
        
        return $this->arResult;
    }
}