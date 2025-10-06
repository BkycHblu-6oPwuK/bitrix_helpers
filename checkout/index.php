<?

use Beeralex\Main\PageHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Оформление заказа");
$APPLICATION->IncludeComponent(
	"beeralex:sale.order.ajax",
	".default",
	array(
		"PAY_FROM_ACCOUNT" => "Y",
		"COUNT_DELIVERY_TAX" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"ALLOW_AUTO_REGISTER" => "Y",
		"SEND_NEW_USER_NOTIFY" => "Y",
		"DELIVERY_NO_AJAX" => "N",
		"TEMPLATE_LOCATION" => "popup",
		"PROP_1" => array(),
		"PATH_TO_BASKET" => PageHelper::getBasketUrl(),
		"PATH_TO_PERSONAL" => "/personal/order/",
		"PATH_TO_PAYMENT" => "/personal/order/payment/",
		"PATH_TO_ORDER" => PageHelper::getCheckoutPageUrl(),
		"SET_TITLE" => "Y",
		"SHOW_ACCOUNT_NUMBER" => "Y",
		"DELIVERY_NO_SESSION" => "Y",
		"COMPATIBLE_MODE" => "N",
		"BASKET_POSITION" => "before",
		"BASKET_IMAGES_SCALING" => "adaptive",
		"SERVICES_IMAGES_SCALING" => "adaptive",
		"USER_CONSENT" => "Y",
		"USER_CONSENT_ID" => "1",
		"USER_CONSENT_IS_CHECKED" => "Y",
		"USER_CONSENT_IS_LOADED" => "Y"
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
