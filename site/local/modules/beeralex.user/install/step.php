<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

if ($errorException = $APPLICATION->getException()) {
    CAdminMessage::showMessage(
        Loc::getMessage('BEERALEX_USER_INSTALL_FAILED') . ': '.$errorException->GetString()
    );
} else {
    CAdminMessage::showNote(
        Loc::getMessage('BEERALEX_USER_INSTALL_SUCCESS')
    );
}