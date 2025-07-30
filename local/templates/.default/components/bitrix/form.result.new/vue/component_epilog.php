<?php

use Itb\Core\Assets\Vite;
use Itb\Core\Helpers\WebHelper;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($request->isAjaxRequest()) {
    if (!defined('PUBLIC_AJAX_MODE')) {
        define('PUBLIC_AJAX_MODE', true);
    }
    if ($request->get($arResult['VUE_DATA']->formIdsMap['formId']) == $arResult['VUE_DATA']->id) {
        WebHelper::jsonAnswer([
            'formNewDto' => $arResult['VUE_DATA']
        ]);
    }
}

Vite::getInstance()->includeAssets(['src/app/formNew/index.js']);
