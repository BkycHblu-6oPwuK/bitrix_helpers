<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\DI\ServiceLocator;
use Beeralex\Notification\Contracts\NotificationTemplateLinkRepositoryContract;

$MODULE_ID = "beeralex.notification";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$sTableID = "tbl_notification_template_links";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

/**
 * @var NotificationTemplateLinkRepositoryContract $repo
 */
$repo = ServiceLocator::getInstance()->get(NotificationTemplateLinkRepositoryContract::class);

// --- Массовые действия ---
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    $action = $request->get("action_button_{$sTableID}");
    foreach ($arID as $id) {
        $id = (int)$id;
        if ($id <= 0) {
            continue;
        }

        switch ($action) {
            case "delete":
                $repo->delete($id);
                break;
            case "activate":
                $repo->activate($id);
                break;
            case "deactivate":
                $repo->deactivate($id);
                break;
        }
    }
}

// --- Фильтры ---
$displayFilter = [
    ["id" => "ID", "name" => "ID", "type" => "number", "default" => true],
    ["id" => "SMS_TEMPLATE_ID", "name" => "Почтовое событие Bitrix", "type" => "string", "default" => true],
    ["id" => "CHANNEL_ID", "name" => "Канал", "type" => "string", "default" => true],
    ["id" => "ACTIVE", "name" => "Активность", "type" => "list", "items" => ["Y" => "Да", "N" => "Нет"]],
];

$filter = [];
$lAdmin->AddFilter($displayFilter, $filter);

// --- Навигация ---
$nav = $lAdmin->getPageNavigation($sTableID);

$listResult = $repo->getList([
    'select' => [
        'ID',
        'SMS_TEMPLATE_ID',
        'CHANNEL_ID',
        'ACTIVE',
        'EVENT_MESSAGE_EVENT_NAME' => 'SMS_TEMPLATE.EVENT_NAME',
        'CHANNEL_CODE' => 'CHANNEL.CODE',
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
    ['id' => 'SMS_TEMPLATE_ID', 'content' => 'Смс шаблон', 'sort' => 'SMS_TEMPLATE_ID', 'default' => true],
    ['id' => 'CHANNEL_ID', 'content' => 'Канал', 'sort' => 'CHANNEL_ID', 'default' => true],
    ['id' => 'ACTIVE', 'content' => 'Активен', 'sort' => 'ACTIVE', 'default' => true],
]);

// --- Заполнение строк ---
foreach ($listResult->fetchAll() as $item) {
    $row = &$lAdmin->AddRow(
        $item['ID'],
        $item,
        "/bitrix/admin/beeralex_notification_template_link.php?ID=" . $item['ID'],
        "Редактировать связь"
    );

    $row->AddField('ID', $item['ID']);
    $row->AddField('SMS_TEMPLATE_ID', '[' . htmlspecialcharsbx($item['SMS_TEMPLATE_ID']) . '] ' . htmlspecialcharsbx($item['EVENT_MESSAGE_EVENT_NAME']));
    $row->AddField('CHANNEL_ID', '[' . htmlspecialcharsbx($item['CHANNEL_ID']) . '] ' . htmlspecialcharsbx($item['CHANNEL_CODE']));
    $row->AddCheckField('ACTIVE', $item['ACTIVE']);

    $actions = [
        [
            "ICON" => "edit",
            "TEXT" => "Редактировать",
            "LINK" => "/bitrix/admin/beeralex_notification_template_link.php?ID=" . $item['ID'],
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
    "activate" => "Активировать",
    "deactivate" => "Деактивировать",
]);

// --- Кнопка добавления ---
$aContext = [
    [
        "TEXT" => "Добавить связь шаблона",
        "LINK" => "/bitrix/admin/beeralex_notification_template_link.php?lang=" . LANG,
        "TITLE" => "Создать новую связь шаблона",
        "ICON" => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

// --- Интерфейс ---
$APPLICATION->SetTitle("Связи шаблонов уведомлений с каналами");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($displayFilter);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
