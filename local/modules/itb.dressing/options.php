<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Itb\Core\Helpers\SaleHelper;
use Itb\Core\Modules\Options\Fields\Select;
use Itb\Core\Modules\Options\Tab;
use Itb\Core\Modules\Options\TabsBuilder;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT < "S") {
    $APPLICATION->AuthForm('Недостаточные права доступа');
}

Loader::includeModule($module_id);
Loader::includeModule('iblock');

$statuses = [
    0 => 'Не выбрано',
];
$payments = [
    0 => 'Не выбрано',
];

$selectedStatus = 0;
$selectedPay = 0;

foreach(SaleHelper::getStatuses() as $status){
    $statuses[$status['ID']] = $status['NAME'];
}

foreach(SaleHelper::getActivePayment() as $payment){
    $payments[$payment['ID']] = $payment['NAME'];
}

$mainTab = new Tab('edit1', 'Общие настройки', 'Отзывы');
$mainTab->addField((new Select('dressing_status', 'Статус', $statuses))->setDefaultValue($selectedStatus)->setLabel('Обязательные'));
$mainTab->addField((new Select('dressing_pay', 'Оплата', $payments))->setDefaultValue($selectedPay));
$accessTab = new Tab("edit2", Loc::getMessage("MAIN_TAB_RIGHTS"), Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
$tabsBuilder = (new TabsBuilder())->addTab($mainTab)->addTab($accessTab);

$tabs = $tabsBuilder->getTabs();

if ($request->isPost() && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        $fileds = $tab->getFields();
        if (!isset($fileds)) {
            continue;
        }
        foreach ($fileds as $filed) {
            if($name = $filed->getName()){
                if ($request["apply"]) {
                    $optionValue = $request->getPost($name);
                    $optionValue = is_array($optionValue) ? implode(",", $optionValue) : $optionValue;
                    Option::set($module_id, $name, $optionValue);
                }
                if ($request["default"]) {
                    Option::set($module_id, $name, $filed->getDefaultValue());
                }
            }
        }
    }
}
// отрисовываем форму, для этого создаем новый экземпляр класса CAdminTabControl, куда и передаём массив с настройками
$tabControl = new CAdminTabControl(
    "tabControl",
    $tabsBuilder->getTabsFormattedArray()
);

// отображаем заголовки закладок
$tabControl->Begin();
?>

<form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>" method="post">
    <? foreach ($tabs as $tab) {
        if ($options = $tab->getOptionsFormattedArray()) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $options);
        }
    }
    $tabControl->BeginNextTab();

    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

    $tabControl->Buttons();
    echo (bitrix_sessid_post());
    ?>
    <input class="adm-btn-save" type="submit" name="apply" value="Применить" />
    <input type="submit" name="default" value="По умолчанию" />
</form>
<?
// обозначаем конец отрисовки формы
$tabControl->End();
