<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\EventTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationLinkEventTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
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
$linkId = (int)$request->getQuery("ID");
$typeRepo = service(NotificationTypeRepositoryContract::class);
$linkRepo = service(NotificationLinkEventTypeRepositoryContract::class);

// --- Получаем существующую связь ---
$link = $linkId ? $linkRepo->getById($linkId) : null;
if ($linkId && !$link) {
    ShowError("Связь не найдена");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    exit;
}

// --- Получение списков типов уведомлений и событий ---
$notificationTypes = $typeRepo->getAllTypes();
$eventTypes = $locator->get(EventTypeRepositoryContract::class)->getByLanguage('ru', ['ID', 'EVENT_NAME', 'NAME'], ['EVENT_NAME' => 'asc']);
// --- Обработка POST-запроса ---
if ($request->isPost() && check_bitrix_sessid()) {
    $data = [
        'EVENT_NAME' => $request->getPost('EVENT_NAME'),
        'EVENT_TYPE_ID' => (int)$request->getPost('EVENT_TYPE_ID'),
    ];
    if (!$link) {
        $resultId = $linkRepo->add($data);
        if ($resultId > 0) {
            $linkId = $resultId;
        } else {
            ShowError("Ошибка при создании связи");
        }
    } else {
        $result = $linkRepo->update($linkId, $data);
        if (!$result) {
            ShowError("Ошибка при обновлении связи");
        }
    }

    if ($linkId) {
        if ($request->getPost("save")) {
            LocalRedirect("/bitrix/admin/beeralex_notification_link_events.php");
        } elseif ($request->getPost("apply")) {
            LocalRedirect("/bitrix/admin/beeralex_notification_link_event.php?ID=" . $linkId . "&apply=Y");
        }
    }
}

// --- Интерфейс ---
$APPLICATION->SetTitle($link ? "Редактирование связи уведомления" : "Создание связи уведомления");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
    [
        "TEXT"  => "Список связей",
        "TITLE" => "Связи уведомлений",
        "LINK"  => "beeralex_notification_link_events.php?lang=" . LANG,
        "ICON"  => "btn_list",
    ],
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = [[
    "DIV" => "edit1",
    "TAB" => "Основные данные",
    "ICON" => "main_user_edit",
    "TITLE" => "Параметры связи уведомления",
]];

$formUrl = $APPLICATION->GetCurPage() . ($link ? "?ID=" . $linkId : "");
?>
<form method="POST" action="<?= $formUrl ?>">
    <?php
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    echo bitrix_sessid_post();
    ?>

    <?php if ($link): ?>
        <tr>
            <td width="40%">ID:</td>
            <td><?= htmlspecialcharsbx($link['ID']) ?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td width="40%">Тип уведомления:</td>
        <td>
            <select name="EVENT_TYPE_ID" style="width:300px;">
                <option value="">-- выберите тип уведомления --</option>
                <?php foreach ($notificationTypes as $type): ?>
                    <option value="<?= $type['ID'] ?>" <?= ($link && $link['EVENT_TYPE_ID'] == $type['ID']) ? 'selected' : '' ?>>
                        [<?= htmlspecialcharsbx($type['CODE']) ?>] <?= htmlspecialcharsbx($type['NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>Почтовое событие Bitrix:</td>
        <td>
            <select name="EVENT_NAME" style="width:300px;">
                <option value="">-- выберите событие --</option>
                <?php foreach ($eventTypes as $event): ?>
                    <option value="<?= $event['EVENT_NAME'] ?>" <?= ($link && $link['EVENT_NAME'] == $event['EVENT_NAME']) ? 'selected' : '' ?>>
                        [<?= htmlspecialcharsbx($event['EVENT_NAME']) ?>] <?= htmlspecialcharsbx($event['NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <?php
    $tabControl->End();
    ?>
    <div style="margin-top:15px;">
        <input type="submit" name="save" value="Сохранить" class="adm-btn-save">
        <input type="submit" name="apply" value="Применить">
    </div>
</form>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
