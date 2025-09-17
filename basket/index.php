<?

use Itb\Main\PageHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
$APPLICATION->IncludeComponent(
    'itb:basket',
    '.default',
    [
        'PATH_TO_ORDER' => PageHelper::getCheckoutPageUrl()
    ]
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
