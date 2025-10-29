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

use App\Main\PageHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($arResult['ORDER'])) {
    \Bitrix\Iblock\Component\Tools::process404('Заказ не найден', true, true, true);
}
?>

<div class="checkout-success-block">
    <div class="checkout-success-block__info">
        <div class="checkout-success-block__info-icon">
            <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="86.5" fill="white" stroke="#111827" stroke-width="2" />
                <path d="M80.5986 104.557L92.5778 116.537L122.526 86.5886" stroke="#0F1523" stroke-width="2" stroke-linecap="square" />
            </svg>
        </div>
        <span class="checkout-success-block__info-message">Номер вашего заказа #<?=$arResult['ORDER_ID']?></span>
        <span class="checkout-success-block__info-text">Перейдите в личный кабинет, чтобы отслеживать статус заказа.</span>
    </div>
    <div class="checkout-success-block__buttons">
        <a href="<?=PageHelper::getProfileOrdersPageUrl() . $arResult['ORDER_ID']?>"><button class="checkout-success-block__submit">Перейти к заказу</button></a>
        <a href="/" class="checkout-success-block__return">На главную</a>
    </div>
</div>