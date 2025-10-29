<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\DI\ServiceLocator;

$MODULE_ID = "beeralex.notification";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT === "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$typeRepo = service(NotificationTypeRepositoryContract::class);
$sTableID = "tbl_notification_types";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

// --- Массовые действия ---
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT === "W") {
    $action = $request->get("action_button_{$sTableID}");

    foreach ($arID as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;

        switch ($action) {
            case "delete":
                $typeRepo->delete($id);
                break;
        }
    }
}

// --- Фильтры ---
$displayFilter = [
    ["id" => "ID", "name" => "ID", "type" => "number", "default" => true],
    ["id" => "CODE", "name" => "Код", "type" => "string", "default" => true],
    ["id" => "NAME", "name" => "Название", "type" => "string", "default" => true],
];
$filter = [];
$lAdmin->AddFilter($displayFilter, $filter);

// --- Навигация ---
$nav = $lAdmin->getPageNavigation($sTableID);

$list = $typeRepo->getList([
    'select' => ['ID', 'CODE', 'NAME'],
    'filter' => $filter,
    'order'  => [$oSort->getField() => $oSort->getOrder()],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit'  => $nav->getLimit(),
]);

$records = $list->fetchAll();
$nav->setRecordCount($list->getCount());
$lAdmin->setNavigation($nav, "", false);

// --- Заголовки ---
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'CODE', 'content' => 'Код', 'sort' => 'CODE', 'default' => true],
    ['id' => 'NAME', 'content' => 'Название', 'sort' => 'NAME', 'default' => true],
]);

// --- Строки таблицы ---
foreach ($records as $item) {
    $row = &$lAdmin->AddRow($item['ID'], $item, "/bitrix/admin/beeralex_notification_type.php?ID=" . $item['ID'], "Редактировать");

    $row->AddField('ID', $item['ID']);
    $row->AddField('CODE', htmlspecialcharsbx($item['CODE']));
    $row->AddField('NAME', htmlspecialcharsbx($item['NAME']));

    $actions = [
        [
            "ICON" => "edit",
            "TEXT" => "Редактировать",
            "LINK" => "/bitrix/admin/beeralex_notification_type.php?ID=" . $item['ID'],
            "DEFAULT" => true,
        ],
        [
            "ICON" => "delete",
            "TEXT" => "Удалить",
            "ACTION" => $lAdmin->ActionDoGroup($item['ID'], "delete"),
        ],
    ];
    $row->AddActions($actions);
}

// --- Массовые действия внизу таблицы ---
$lAdmin->AddGroupActionTable([
    "delete" => "Удалить выбранные",
]);

// --- Кнопка «Добавить тип» ---
$aContext = [
    [
        "TEXT"  => "Добавить тип",
        "LINK"  => "/bitrix/admin/beeralex_notification_type.php?lang=" . LANG,
        "TITLE" => "Создать новый тип уведомления",
        "ICON"  => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle("Типы уведомлений");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// --- Отображение списка ---
$lAdmin->DisplayFilter($displayFilter);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
