<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Notification\Contracts\NotificationTemplateLinkRepositoryContract;
use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Sms\TemplateTable;

$MODULE_ID = "beeralex.notification";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$linkId = (int)$request->getQuery("ID");

$locator = ServiceLocator::getInstance();
/**
 * @var NotificationTemplateLinkRepositoryContract
 */
$linkRepo = $locator->get(NotificationTemplateLinkRepositoryContract::class);
/**
 * @var NotificationChannelRepositoryContract
 */
$channelRepo = $locator->get(NotificationChannelRepositoryContract::class);

// --- Получаем существующую связь ---
$link = $linkId ? $linkRepo->getById($linkId) : null;
if ($linkId && !$link) {
    ShowError("Связь шаблона не найдена");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    exit;
}

// --- Получение списков каналов и шаблонов ---
$channels = $channelRepo->getNonEmailChannels();

$templates = TemplateTable::getList([
    'select' => ['ID', 'EVENT_NAME'],
    'order' => ['EVENT_NAME' => 'asc']
])->fetchAll();

if ($request->isPost() && check_bitrix_sessid()) {
    $data = [
        'CHANNEL_ID' => (int)$request->getPost('CHANNEL_ID'),
        'SMS_TEMPLATE_ID' => $request->getPost('SMS_TEMPLATE_ID'),
        'ACTIVE' => $request->getPost('ACTIVE') === 'Y' ? 'Y' : 'N',
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
            LocalRedirect("/bitrix/admin/beeralex_notification_template_links.php");
        } elseif ($request->getPost("apply")) {
            LocalRedirect("/bitrix/admin/beeralex_notification_template_links.php?ID=" . $linkId . "&apply=Y");
        }
    }
}

// --- Интерфейс ---
$APPLICATION->SetTitle($link ? "Редактирование связи шаблона" : "Создание связи шаблона");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
    [
        "TEXT"  => "Список связей шаблонов",
        "TITLE" => "Связи шаблонов уведомлений",
        "LINK"  => "beeralex_notification_template_links.php?lang=" . LANG,
        "ICON"  => "btn_list",
    ],
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = [[
    "DIV" => "edit1",
    "TAB" => "Основные данные",
    "ICON" => "main_user_edit",
    "TITLE" => "Параметры связи шаблона уведомления",
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
        <td width="40%">Канал уведомления:</td>
        <td>
            <select name="CHANNEL_ID" style="width:300px;">
                <option value="">-- выберите канал --</option>
                <?php foreach ($channels as $channel): ?>
                    <option value="<?= $channel['ID'] ?>" <?= ($link && $link['CHANNEL_ID'] == $channel['ID']) ? 'selected' : '' ?>>
                        [<?= htmlspecialcharsbx($channel['CODE']) ?>] <?= htmlspecialcharsbx($channel['NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>Почтовый шаблон Bitrix (для SMS / Email):</td>
        <td>
            <select name="SMS_TEMPLATE_ID" style="width:300px;">
                <option value="">-- выберите шаблон --</option>
                <?php foreach ($templates as $template): ?>
                    <option value="<?= htmlspecialcharsbx($template['ID']) ?>"
                        <?= ($link && $link['SMS_TEMPLATE_ID'] === $template['ID']) ? 'selected' : '' ?>>
                        [<?= htmlspecialcharsbx($template['ID']) ?>] <?= htmlspecialcharsbx($template['EVENT_NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>Активность:</td>
        <td>
            <input type="checkbox" name="ACTIVE" value="Y" <?= (!$link || $link['ACTIVE'] === 'Y') ? 'checked' : '' ?>>
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
