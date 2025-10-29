<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Профиль");

$APPLICATION->IncludeComponent(
	"beeralex:profile", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/account/",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"SEF_URL_TEMPLATES" => array(
			"index" => "/",
		)
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");