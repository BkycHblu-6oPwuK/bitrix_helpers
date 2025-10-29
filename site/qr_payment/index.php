<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$orderId = trim($_GET['ORDER_ID']);

if (!empty($orderId)) {
    $APPLICATION->IncludeComponent('beeralex:qr_show', '.default', [
        'ORDER_ID' => $orderId,
        "CHECK_PAYMENT" => 'Y',
        "REDIRECT_URL" => '/thanks_for_buying.php',
        'SHOW_PUSH' => true,
    ]);
} else {
    LocalRedirect("/");
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
