<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
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
 * @var NotificationChannelRepositoryContract
 */
$repo = ServiceLocator::getInstance()->get(NotificationChannelRepositoryContract::class);

$channelId = (int)$request->getQuery("ID");
$channel = $channelId ? $repo->getById($channelId) : null;

if ($request->isPost() && check_bitrix_sessid()) {
    $data = [
        'CODE'   => trim($request->getPost('CODE')),
        'NAME'   => trim($request->getPost('NAME')),
        'ACTIVE' => $request->getPost('ACTIVE') === 'Y' ? 'Y' : 'N',
    ];

    if ($channel) {
        $repo->update($channelId, $data);
    } else {
        $channelId = $repo->add($data);
    }

    if ($request->getPost("save")) {
        LocalRedirect("/bitrix/admin/beeralex_notification_channels.php?lang=" . LANG);
    } elseif ($request->getPost("apply")) {
        LocalRedirect("/bitrix/admin/beeralex_notification_channel.php?ID=" . $channelId . "&lang=" . LANG . "&apply=Y");
    }
}

$APPLICATION->SetTitle($channel ? "Редактирование канала" : "Создание канала");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
    [
        "TEXT"  => "Список каналов",
        "TITLE" => "Каналы уведомлений",
        "LINK"  => "beeralex_notification_channels.php?lang=" . LANG,
        "ICON"  => "btn_list",
    ],
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = [[
    "DIV" => "edit1",
    "TAB" => "Параметры",
    "ICON" => "main_user_edit",
    "TITLE" => "Настройки канала уведомлений",
]];

$formUrl = $APPLICATION->GetCurPage() . ($channelId ? "?ID=" . $channelId : "");
?>

<form method="POST" action="<?= htmlspecialcharsbx($formUrl) ?>">
    <?php
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    echo bitrix_sessid_post();
    ?>

    <?php if ($channel): ?>
        <tr>
            <td width="40%">ID:</td>
            <td><?= htmlspecialcharsbx($channel['ID']) ?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td>Код канала (email, sms, telegram):</td>
        <td>
            <input type="text" name="CODE" value="<?= htmlspecialcharsbx($channel['CODE'] ?? '') ?>" size="40">
        </td>
    </tr>

    <tr>
        <td>Название:</td>
        <td>
            <input type="text" name="NAME" value="<?= htmlspecialcharsbx($channel['NAME'] ?? '') ?>" size="60">
        </td>
    </tr>

    <tr>
        <td>Активность:</td>
        <td>
            <input type="checkbox" name="ACTIVE" value="Y" <?= ($channel['ACTIVE'] ?? 'Y') === 'Y' ? 'checked' : '' ?>>
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
