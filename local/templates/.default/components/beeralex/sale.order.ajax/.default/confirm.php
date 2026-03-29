<?php

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $
 * @var Service $paySystem 
 */

use Beeralex\Api\ApiResult;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\service(ApiResult::class)->addPageData(
    $arResult['ORDER'],
    'order'
);
?>