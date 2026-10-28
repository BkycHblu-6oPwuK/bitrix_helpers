<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */

if (!empty($arResult['ITEMS'])) {
    foreach ($arResult['ITEMS'] as $item) {
        if (!empty($item['PROPERTIES']['CONTENT']['~VALUE'])) {
            $APPLICATION->IncludeComponent(
                "sprint.editor:blocks",
                "",
                [
                    "JSON" => $item['PROPERTIES']['CONTENT']['~VALUE'],
                ],
                false
            );
        }
    }
}
$component = $this->getComponent();
$arResult['BLOCKS'] = $GLOBALS['_CONTENT_BLOCKS'] ?? [];
$component->setResultCacheKeys(['BLOCKS']);

unset($GLOBALS['_CONTENT_BLOCKS'], $GLOBALS['_CONTENT_BLOCK_DEPTH']);