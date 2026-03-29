<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\NotificationLinkEventTypeRepositoryContract;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\DI\ServiceLocator;

$MODULE_ID = "beeralex.notification";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$sTableID = "tbl_notification_link_event";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);
$linkRepo = service(NotificationLinkEventTypeRepositoryContract::class);

// --- Массовые действия ---
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    $action = $request->get("action_button_{$sTableID}");
    foreach ($arID as $id) {
        $id = (int)$id;
        if ($action === "delete" && $id > 0) {
            $linkRepo->delete($id);
        }
    }
}

// --- Фильтры ---
$displayFilter = [
    ["id" => "ID", "name" => "ID", "type" => "number", "default" => true],
    ["id" => "EVENT_TYPE_ID", "name" => "Тип уведомления", "type" => "string", "default" => true],
    ["id" => "EVENT_ID", "name" => "Событие Bitrix", "type" => "string", "default" => true],
];

$filter = [
    'EVENT.LID' => 'ru',
];
$lAdmin->AddFilter($displayFilter, $filter);

// --- Навигация ---
$nav = $lAdmin->getPageNavigation($sTableID);

$listResult = $linkRepo->getList([
    'select' => [
        'ID',
        'EVENT_NAME',
        'EVENT_TYPE_ID',
        'EVENT_TYPE_NAME' => 'EVENT_TYPE.NAME',
        'EVENT_TYPE_CODE' => 'EVENT_TYPE.CODE',
        'EVENT_TITLE' => 'EVENT.NAME',
    ],
    'filter' => $filter,
    'order' => [$oSort->getField() => $oSort->getOrder()],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
]);

$totalCount = $listResult->getCount();

$nav->setRecordCount($totalCount);
$lAdmin->setNavigation($nav, "", false);

// --- Заголовки ---
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'EVENT_TYPE_ID', 'content' => 'Тип уведомления', 'sort' => 'EVENT_TYPE_ID', 'default' => true],
    ['id' => 'EVENT_ID', 'content' => 'Почтовое событие Bitrix', 'sort' => 'EVENT_ID', 'default' => true],
]);

// --- Заполнение строк ---
foreach ($listResult->fetchAll() as $item) {
    $row = &$lAdmin->AddRow(
        $item['ID'],
        $item,
        "/bitrix/admin/beeralex_notification_link_event.php?ID=" . $item['ID'],
        "Редактировать"
    );

    $row->AddField('ID', $item['ID']);
    $row->AddField(
        'EVENT_TYPE_ID',
        '[' . htmlspecialcharsbx($item['EVENT_TYPE_CODE']) . '] ' . htmlspecialcharsbx($item['EVENT_TYPE_NAME'])
    );
    $row->AddField(
        'EVENT_ID',
        '[' . htmlspecialcharsbx($item['EVENT_NAME']) . '] ' . htmlspecialcharsbx($item['EVENT_TITLE'])
    );

    $actions = [
        [
            "ICON" => "edit",
            "TEXT" => "Редактировать",
            "LINK" => "/bitrix/admin/beeralex_notification_link_event.php?ID=" . $item['ID'],
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

// --- Групповые действия ---
$lAdmin->AddGroupActionTable([
    "delete" => "Удалить выбранные",
]);

// --- Кнопка добавления ---
$aContext = [
    [
        "TEXT" => "Добавить связь",
        "LINK" => "/bitrix/admin/beeralex_notification_link_event.php?lang=" . LANG,
        "TITLE" => "Создать новую связь",
        "ICON" => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

// --- Интерфейс ---
$APPLICATION->SetTitle("Связи уведомлений с событиями Bitrix");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($displayFilter);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
