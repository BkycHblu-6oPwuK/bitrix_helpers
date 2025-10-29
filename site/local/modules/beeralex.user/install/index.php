<?php

use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\User\Auth\Table\ExternalAuthTable;
use Beeralex\User\EventHandlers;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class beeralex_user extends CModule
{

    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID           = 'beeralex.user';
            $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME         = Loc::getMessage('BEERALEX_USER_NAME');
            $this->MODULE_DESCRIPTION  = Loc::getMessage('BEERALEX_USER_DESCRIPTION');
            $this->PARTNER_NAME = 'Beeralex';
            $this->PARTNER_URI = '#';
        } else {
            CAdminMessage::showMessage(
                Loc::getMessage('BEERALEX_USER_FILE_NOT_FOUND') . ' version.php'
            );
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            $this->InstallDB();
            $this->InstallEvents();
            //$this->InstallFiles();
        } else {
            $APPLICATION->ThrowException('Нет поддержки d7 в главном модуле');
        }
        $APPLICATION->IncludeAdminFile(
            'Установка модуля',
            __DIR__ . '/step.php'
        );
    }

    protected function isVersionD7()
    {
        return version_compare(ModuleManager::getVersion('main'), '14.0.0') >= 0;
    }

    public function InstallFiles()
    {
        $moduleDir = __DIR__;
        $sourceDir = $moduleDir . '/files';
        $targetDir = Application::getDocumentRoot();

        FilesHelper::copyRecursive($sourceDir, $targetDir);
    }

    public function InstallDB() {}

    public function UnInstallDB() {}

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler('socialservices', 'OnAuthServicesBuildList', $this->MODULE_ID, EventHandlers::class, 'onAuthServicesBuildList');
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('socialservices', 'OnAuthServicesBuildList', $this->MODULE_ID, EventHandlers::class, 'onAuthServicesBuildList');
    }

    public function doUninstall()
    {
        global $APPLICATION;

        $context = \Bitrix\Main\Context::getCurrent();
        $request = $context->getRequest();
        Loader::includeModule($this->MODULE_ID);
        if ($request['step'] < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage('BEERALEX_USER_UNINSTALL_TITLE'), __DIR__ . '/unstep1.php');
        } else {
            if ($request['savedata'] !== 'Y') {
                $this->UnInstallDB();
            }
            $this->UnInstallEvents();

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage('BEERALEX_USER_UNISTALL_TITLE'), __DIR__ . '/unstep2.php');
        }
    }
}
