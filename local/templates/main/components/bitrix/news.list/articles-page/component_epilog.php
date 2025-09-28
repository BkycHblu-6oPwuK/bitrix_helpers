<?php
use Itb\Core\Assets\Vite;
use Itb\Core\Config;
use Itb\Core\Helpers\WebHelper;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->isAjaxRequest())
{
    if (!defined('PUBLIC_AJAX_MODE'))
    {
        define('PUBLIC_AJAX_MODE', true);
    }
    if ($request->get('action') === Config::getInstance()->actionLoadItems)
    {
        WebHelper::jsonAnswer([
            'articlesList' => $arResult,
        ]);
    }
}

Vite::getInstance()->includeAssets(['src/app/articles/index.js']);
?>