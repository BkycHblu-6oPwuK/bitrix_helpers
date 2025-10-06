<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexVideo extends CBitrixComponent
{

    /** @inheritDoc */
    public function executeComponent()
    {
        if(!$this->arParams['VIDEO_LINK']) return;
        if(!$this->arParams['PREVIEW_ID']) return;
        if ($this->startResultCache()) {
            $this->arResult['video_link'] = $this->arParams['VIDEO_LINK'];
            $this->arResult['preview'] = $this->arParams['PREVIEW_ID'] ? $this->getPreview($this->arParams['PREVIEW_ID']) : null;
            $this->arResult['text'] = $this->arParams['TEXT'];
            $this->arResult['title'] = $this->arParams['TITLE'];
            $this->includeComponentTemplate();
        }
    }

    private function getPreview(int $previewId)
    {
        return CFile::GetPath($previewId);
    }
}
