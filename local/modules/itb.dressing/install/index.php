<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class itb_dressing extends CModule
{
    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID           = 'itb.dressing';
            $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME         = Loc::getMessage('DRESSING_NAME');
            $this->MODULE_DESCRIPTION  = Loc::getMessage('DRESSING_DESCRIPTION');
            $this->PARTNER_NAME = 'Itb';
            $this->PARTNER_URI = '#';
        } else {
            CAdminMessage::showMessage(
                Loc::getMessage('DRESSING_FILE_NOT_FOUND') . ' version.php'
            );
        }
    }

    public function doInstall()
    {
        if (CheckVersion(ModuleManager::getVersion('main'), '23.00.00')) {
            global $APPLICATION;
            ModuleManager::registerModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('DRESSING_INSTALL_TITLE') . ' «' . Loc::getMessage('DRESSING_NAME') . '»',
                __DIR__ . '/step.php'
            );
        } else {
            CAdminMessage::showMessage(
                Loc::getMessage('DRESSING_INSTALL_ERROR')
            );
            return;
        }
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage('DRESSING_UNISTALL_TITLE'), __DIR__ . '/unstep2.php');
    }
}
