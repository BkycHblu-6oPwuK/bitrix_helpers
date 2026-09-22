<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

if ($errorException = $APPLICATION->getException()) {
    CAdminMessage::showMessage(
        (Loc::getMessage('BEERALEX_APISHIP_INSTALL_FAILED') ?: 'Ошибка установки модуля') . ': ' . $errorException->GetString()
    );
} else {
    CAdminMessage::showNote(
        Loc::getMessage('BEERALEX_APISHIP_INSTALL_SUCCESS') ?: 'Модуль успешно установлен'
    );
}
