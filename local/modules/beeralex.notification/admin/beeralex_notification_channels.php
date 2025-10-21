<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;

$MODULE_ID = "beeralex.notification";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$sTableID = "tbl_notification_channels";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

/**
 * @var NotificationChannelRepositoryContract
 */
$repository = ServiceLocator::getInstance()->get(NotificationChannelRepositoryContract::class);

// --- Групповые действия ---
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    foreach ($arID as $ID) {
        if ($ID <= 0) continue;

        $action = $request->get("action_button_{$sTableID}");
        switch ($action) {
            case "delete":
                $repository->delete($ID);
                break;
            case "activate":
                $repository->activate($ID);
                break;
            case "deactivate":
                $repository->deactivate($ID);
                break;
        }
    }
}

// --- Фильтры ---
$displayFilter = [
    ["id" => "ID", "name" => "ID", "type" => "number", "default" => true],
    ["id" => "CODE", "name" => "Код", "type" => "string", "default" => true],
    ["id" => "NAME", "name" => "Название", "type" => "string", "default" => true],
    ["id" => "ACTIVE", "name" => "Активность", "type" => "list", "items" => [
        "Y" => "Активен",
        "N" => "Неактивен",
    ]],
];
$filter = [];
$lAdmin->AddFilter($displayFilter, $filter);

// --- Навигация ---
$nav = $lAdmin->getPageNavigation($sTableID);

// --- Получение данных через репозиторий ---
$result = $repository->getList([
    'select' => ['ID', 'CODE', 'NAME', 'ACTIVE', 'CREATED_AT'],
    'filter' => $filter,
    'order' => [$oSort->getField() => $oSort->getOrder()],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
]);

$nav->setRecordCount($result->getCount());
$lAdmin->setNavigation($nav, "", false);

// --- Заголовки ---
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'CODE', 'content' => 'Код', 'sort' => 'CODE', 'default' => true],
    ['id' => 'NAME', 'content' => 'Название', 'sort' => 'NAME', 'default' => true],
    ['id' => 'ACTIVE', 'content' => 'Активность', 'sort' => 'ACTIVE', 'default' => true],
    ['id' => 'CREATED_AT', 'content' => 'Создан', 'sort' => 'CREATED_AT'],
]);

foreach ($result as $item) {
    $row = &$lAdmin->AddRow(
        $item['ID'],
        $item,
        "/bitrix/admin/beeralex_notification_channel.php?ID={$item['ID']}",
        "Редактировать"
    );

    $row->AddField('ID', $item['ID']);
    $row->AddField('CODE', htmlspecialcharsbx($item['CODE']));
    $row->AddField('NAME', htmlspecialcharsbx($item['NAME']));
    $row->AddField('ACTIVE', $item['ACTIVE'] === 'Y' ? 'Да' : 'Нет');
    $row->AddField('CREATED_AT', $item['CREATED_AT'] ? $item['CREATED_AT']->toString() : '');

    $actions = [
        [
            "ICON" => "edit",
            "TEXT" => "Редактировать",
            "LINK" => "/bitrix/admin/beeralex_notification_channel.php?ID={$item['ID']}",
            "DEFAULT" => true,
        ],
        [
            "ICON" => "delete",
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Удалить канал?')) {$lAdmin->ActionDoGroup($item['ID'], 'delete')}",
        ],
    ];
    $row->AddActions($actions);
}

// --- Кнопки ---
$aContext = [
    [
        "TEXT" => "Добавить канал",
        "LINK" => "/bitrix/admin/beeralex_notification_channel.php?lang=" . LANG,
        "TITLE" => "Создать новый канал уведомлений",
        "ICON" => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->AddGroupActionTable([
    "delete" => "Удалить выбранные",
    "activate" => "Активировать",
    "deactivate" => "Деактивировать",
]);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle("Каналы уведомлений");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($displayFilter);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
