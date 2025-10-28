<?php

use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\Notification\Events\EventHandlers;
use Beeralex\Notification\Tables\NotificationChannelTable;
use Beeralex\Notification\Tables\NotificationCodeTable;
use Beeralex\Notification\Tables\NotificationLinkEventTypeTable;
use Beeralex\Notification\Tables\NotificationsTable;
use Beeralex\Notification\Tables\NotificationTemplateLinkTable;
use Beeralex\Notification\Tables\NotificationTypeTable;
use Beeralex\Notification\Tables\UserNotificationPreferenceTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class beeralex_notification extends CModule
{

    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID           = 'beeralex.notification';
            $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME         = Loc::getMessage('BEERALEX_NOTIFICATION_NAME');
            $this->MODULE_DESCRIPTION  = Loc::getMessage('BEERALEX_NOTIFICATION_DESCRIPTION');
            $this->PARTNER_NAME = 'Beeralex';
            $this->PARTNER_URI = '#';
        } else {
            CAdminMessage::showMessage(
                Loc::getMessage('BEERALEX_NOTIFICATION_FILE_NOT_FOUND') . ' version.php'
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
            $this->InstallFiles();
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

    public function InstallDB()
    {
        NotificationChannelTable::createTable();
        NotificationCodeTable::createTable();
        NotificationLinkEventTypeTable::createTable();
        NotificationsTable::createTable();
        NotificationTypeTable::createTable();
        UserNotificationPreferenceTable::createTable();
        NotificationTemplateLinkTable::createTable();
    }

    public function UnInstallDB()
    {
        NotificationChannelTable::dropTable();
        NotificationCodeTable::dropTable();
        NotificationLinkEventTypeTable::dropTable();
        NotificationsTable::dropTable();
        NotificationTypeTable::dropTable();
        UserNotificationPreferenceTable::dropTable();
        NotificationTemplateLinkTable::dropTable();
    }

    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnBeforeEventAdd',
            $this->MODULE_ID,
            EventHandlers::class,
            'mainOnBeforeEventAdd'
        );
        $eventManager->registerEventHandler(
            'main',
            'onBeforeSendSms',
            $this->MODULE_ID,
            EventHandlers::class,
            'mainOnBeforeSendSms'
        );
        // $eventManager->registerEventHandler(
        //     'main',
        //     'OnPageStart',
        //     $this->MODULE_ID,
        //     EventHandlers::class,
        //     'mainOnPageStart'
        // );
    }

    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBeforeEventAdd',
            $this->MODULE_ID,
            EventHandlers::class,
            'mainOnBeforeEventAdd'
        );
        $eventManager->unRegisterEventHandler(
            'main',
            'onBeforeSendSms',
            $this->MODULE_ID,
            EventHandlers::class,
            'mainOnBeforeSendSms'
        );
        // $eventManager->unRegisterEventHandler(
        //     'main',
        //     'OnPageStart',
        //     $this->MODULE_ID,
        //     EventHandlers::class,
        //     'mainOnPageStart'
        // );
    }

    public function doUninstall()
    {
        global $APPLICATION;

        $context = \Bitrix\Main\Context::getCurrent();
        $request = $context->getRequest();
        Loader::includeModule($this->MODULE_ID);
        if ($request['step'] < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage('BEERALEX_NOTIFICATION_UNINSTALL_TITLE'), __DIR__ . '/unstep1.php');
        } else {
            if ($request['savedata'] !== 'Y') {
                $this->UnInstallDB();
            }
            $this->UnInstallEvents();

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage('BEERALEX_NOTIFICATION_UNISTALL_TITLE'), __DIR__ . '/unstep2.php');
        }
    }
}
