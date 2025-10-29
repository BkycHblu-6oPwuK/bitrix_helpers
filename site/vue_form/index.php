<?php

use App\Main\PageHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->IncludeComponent(
	"bitrix:form.result.new",
	"vue",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
        "AJAX_MODE" => 'Y',
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "N", // когда SEF_MODE = N, то при успехе битрикс сам прокинет WEB_FORM_ID в гет параметры
		"SUCCESS_URL" => '',
		"USE_EXTENDED_ERRORS" => "Y",
		"VARIABLE_ALIASES" => Array("RESULT_ID"=>"RESULT_ID","WEB_FORM_ID"=>"WEB_FORM_ID"),
		"WEB_FORM_ID" => "1"
	)
);

$APPLICATION->IncludeComponent(
	"bitrix:form.result.new",
	"vue",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
        "AJAX_MODE" => 'Y',
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "Y", // когда SEF_MODE = Y, то нужно в SUCCESS_URL передать WEB_FORM_ID в гет параметрах, макрос #WEB_FORM_ID# битрикс подменяет в компоненте
		"SUCCESS_URL" => PageHelper::getCurUri()->getPath() . '?WEB_FORM_ID=#WEB_FORM_ID#',
		"USE_EXTENDED_ERRORS" => "Y",
		"VARIABLE_ALIASES" => Array("RESULT_ID"=>"RESULT_ID","WEB_FORM_ID"=>"WEB_FORM_ID"),
		"WEB_FORM_ID" => "2"
	)
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");