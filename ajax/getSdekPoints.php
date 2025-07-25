<?php

use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!check_bitrix_sessid()) {
    die(Json::encode(['error' => 'Invalid session'])); 
}

$request = Context::getCurrent()->getRequest();

$city = trim(strip_tags($request->get('city') ?? ''));
if (!empty($city)) {
    $_SESSION['IPOLSDEK_city'] = $city;
    $APPLICATION->IncludeComponent(
        "ipol:ipol.sdekPickup",
        "ajax",
        array(
            "CNT_BASKET" => "N",
            "CNT_DELIV" => "Y",
            "COUNTRIES" => array(),
            "FORBIDDEN" => array(),
            "MODE" => "both",
            "NOMAPS" => "N",
            "PAYER" => "1",
            "PAYSYSTEM" => "1",
            "SEARCH_ADDRESS" => "N"
        )
    );
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
