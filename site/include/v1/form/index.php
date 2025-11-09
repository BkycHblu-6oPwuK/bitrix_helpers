<?php

global $APPLICATION;

if(!$formId) {
    throw new \RuntimeException("param formId is required");
}

$isContentAction = $isContentAction ?? false;

$APPLICATION->IncludeComponent(
	"beeralex:form.result.new",
	".default",
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
        "SEF_FOLDER" => "/",
		"SUCCESS_URL" => 'api/v1/web-form/?WEB_FORM_ID=#WEB_FORM_ID#',
		"USE_EXTENDED_ERRORS" => "Y",
		"VARIABLE_ALIASES" => Array("RESULT_ID"=>"RESULT_ID","WEB_FORM_ID"=>"WEB_FORM_ID"),
		"WEB_FORM_ID" => $formId,
        "IS_CONTENT_ACTION" => $isContentAction,
	)
);