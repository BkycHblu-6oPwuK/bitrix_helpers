<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

if ($ex = $APPLICATION->GetException()) {
    $message = new CAdminMessage([
        'TYPE'    => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_UNINST_ERROR'),
        'DETAILS' => $ex->GetString(),
        'HTML'    => true,
    ]);
    echo $message->Show();
} else {
    $message = new CAdminMessage([
        'TYPE'    => 'OK',
        'MESSAGE' => Loc::getMessage('MOD_UNINST_OK'),
    ]);
    echo $message->Show();
}
