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
/**
 * @var NotificationTypeRepositoryContract
 */
$typeRepo = ServiceLocator::getInstance()->get(NotificationTypeRepositoryContract::class);

$typeId = (int)$request->getQuery("ID");
$type = null;

if ($typeId > 0) {
    $type = $typeRepo->getById($typeId);
    if (!$type) {
        ShowError("Тип уведомления не найден");
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
        exit;
    }
}

if ($request->isPost() && check_bitrix_sessid()) {
    $data = [
        'CODE' => trim($request->getPost('CODE')),
        'NAME' => trim($request->getPost('NAME')),
    ];

    if (!$data['CODE'] || !$data['NAME']) {
        ShowError("Поля CODE и NAME обязательны для заполнения");
    } else {
        if ($type) {
            $result = $typeRepo->update($typeId, $data);
        } else {
            $typeId = $typeRepo->add($data);
            $result = $typeId > 0;
        }

        if ($result) {
            if ($request->getPost("save")) {
                LocalRedirect("/bitrix/admin/beeralex_notification_types.php");
            } elseif ($request->getPost("apply")) {
                LocalRedirect("/bitrix/admin/beeralex_notification_type.php?ID=" . $typeId . "&apply=Y");
            }
        } else {
            ShowError("Ошибка при сохранении данных");
        }
    }
}

$APPLICATION->SetTitle($type ? "Редактирование типа уведомления" : "Создание типа уведомления");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// --- Верхнее меню ---
$aMenu = [
    [
        "TEXT"  => "Список типов",
        "TITLE" => "Типы уведомлений",
        "LINK"  => "beeralex_notification_types.php?lang=" . LANG,
        "ICON"  => "btn_list",
    ],
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

// --- Вкладки ---
$aTabs = [[
    "DIV" => "edit1",
    "TAB" => "Основные данные",
    "ICON" => "main_user_edit",
    "TITLE" => "Параметры типа уведомления",
]];

$formUrl = $APPLICATION->GetCurPage() . ($type ? "?ID=" . $typeId : "");
?>

<form method="POST" action="<?= htmlspecialcharsbx($formUrl) ?>">
    <?php
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    echo bitrix_sessid_post();
    ?>

    <?php if ($type): ?>
        <tr>
            <td width="40%">ID:</td>
            <td><?= htmlspecialcharsbx($type['ID']) ?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td width="40%">Код (CODE):</td>
        <td>
            <input type="text" name="CODE" value="<?= htmlspecialcharsbx($type['CODE'] ?? '') ?>" size="40">
        </td>
    </tr>

    <tr>
        <td>Название (NAME):</td>
        <td>
            <input type="text" name="NAME" value="<?= htmlspecialcharsbx($type['NAME'] ?? '') ?>" size="60">
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
