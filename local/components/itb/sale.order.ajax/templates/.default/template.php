<?php
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */

use Bitrix\Main\Context;
use Itb\Main\PageHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = Context::getCurrent()->getRequest();

if (strlen($request->get('ORDER_ID')) > 0) {
    require __DIR__ . '/confirm.php';
} elseif ($arResult['SHOW_EMPTY_BASKET']) {
    LocalRedirect(PageHelper::getBasketUrl());
} else {
    require __DIR__ . '/order.php';
}
