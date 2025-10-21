<?

if (!check_bitrix_sessid()) {
    return;
}

if ($ex = $APPLICATION->GetException()) {
    $message = new CAdminMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage('MOD_UNINST_ERROR'),
        'DETAILS' => $ex->GetString(),
        'HTML' => true,
    ]);

    echo $message->Show();
} else {
    $message = new CAdminMessage([
        'TYPE' => 'OK',
        'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage('MOD_UNINST_OK'),
    ]);

    echo $message->Show();
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?= \Bitrix\Main\Localization\Loc::getMessage('MOD_BACK') ?>">
</form>