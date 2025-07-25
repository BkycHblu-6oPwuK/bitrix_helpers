<?php

use Bitrix\Iblock\SectionTable;
use Itb\Catalog\CatalogSection;
use Itb\Core\Helpers\IblockHelper;

final class Options
{
    public $iblock_type;
    public $iblock_id;
    public $element_id;
    public $element_code;
    public $section_id;
    public $section_code;
    public $section_url;
    public $detail_url;
    public $is_quick_view;
    public array $section_path = [];

    public function __construct(array $arParams)
    {
        $this->iblock_type = $arParams['IBLOCK_TYPE'];
        $this->iblock_id = (int)$arParams['IBLOCK_ID'];
        $this->element_id = (int)$arParams['ELEMENT_ID'];
        $this->element_code = $arParams['ELEMENT_CODE'];
        $this->section_id = (int)$arParams['SECTION_ID'];
        $this->section_code = $arParams['SECTION_CODE'];
        $this->section_url = $arParams['SECTION_URL'];
        $this->detail_url = $arParams['DETAIL_URL'];
        $this->is_quick_view = (bool)$arParams['IS_QUICK_VIEW'];
        $this->initParamsByCode();
    }

    private function initParamsByCode()
    {
        if(!$this->element_id && $this->element_code){
            $result = IblockHelper::getElementApiTable($this->iblock_id)::query()->setSelect(['ID','IBLOCK_SECTION_ID'])->where('CODE', $this->element_code)->setCacheTtl(360000)->exec()->fetch();
            $this->element_id = (int)$result['ID'];
            $this->section_id = (int)$result['IBLOCK_SECTION_ID'];
        }
        if(!$this->section_id && $this->section_code){
            $this->section_id = (int)SectionTable::query()->setSelect(['ID'])->where('CODE', $this->section_code)->setCacheTtl(360000)->exec()->fetch()['ID'];
        }
        if(!$this->is_quick_view){
            $this->section_path = CatalogSection::getPath($this->section_id);
        }
        //$item = [
        //    'IBLOCK_SECTION_ID' => $this->section_id,
        //    'SECTION_CODE' => $this->section_code,
        //    'IBLOCK_ID' => $this->iblock_id,
        //    'ELEMENT_ID' => $this->element_id,
        //    'ID' => $this->element_id,
        //    'ELEMENT_CODE' => $this->element_code,
        //    'CODE' => $this->element_code,
        //];
        //$this->section_url = \CIBlock::ReplaceDetailUrl($this->section_url,  $item ,  false ,  'E' );
        //$this->detail_url = \CIBlock::ReplaceDetailUrl($this->detail_url,  $item ,  false ,  'E' );
    }
}