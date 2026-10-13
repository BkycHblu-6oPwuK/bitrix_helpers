<?php

use Beeralex\Apiship\Delivery\ApiShipHandler;
use Beeralex\Apiship\EventHandlers;
use Beeralex\Apiship\Model\OrderTable;
use Beeralex\Apiship\Model\StatusLogTable;
use Beeralex\Apiship\Model\WebhookLogTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class beeralex_apiship extends CModule
{
    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include __DIR__ . '/version.php';
            $this->MODULE_ID           = 'beeralex.apiship';
            $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME         = Loc::getMessage('BEERALEX_APISHIP_NAME') ?: 'Beeralex ApiShip доставка';
            $this->MODULE_DESCRIPTION  = Loc::getMessage('BEERALEX_APISHIP_DESCRIPTION') ?: 'Единая интеграция служб доставки через ApiShip';
            $this->PARTNER_NAME        = 'Beeralex';
            $this->PARTNER_URI         = '#';
        } else {
            CAdminMessage::showMessage(
                (Loc::getMessage('BEERALEX_APISHIP_FILE_NOT_FOUND') ?: 'Файл не найден') . ' version.php'
            );
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if ($this->checkRequirements()) {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            $this->InstallDB();
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(
                'Нет поддержки d7 в главном модуле или не установлен модуль beeralex.core'
            );
        }
        $APPLICATION->IncludeAdminFile('Установка модуля', __DIR__ . '/step.php');
    }

    public function checkRequirements(): bool
    {
        return version_compare(ModuleManager::getVersion('main'), '14.00.00') >= 0
            && Loader::includeModule('beeralex.core');
    }

    public function InstallDB()
    {
        foreach ([OrderTable::class, WebhookLogTable::class, StatusLogTable::class] as $table) {
            $table::createTable();
        }
    }

    public function UnInstallDB()
    {
        foreach ([StatusLogTable::class, WebhookLogTable::class, OrderTable::class] as $table) {
            $table::dropTable();
        }
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            $this->MODULE_ID,
            ApiShipHandler::class,
            'onSaleDeliveryHandlersClassNamesBuildList'
        );
        $eventManager->registerEventHandler(
            'sale',
            'OnSaleStatusOrderChange',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleStatusOrderChange'
        );
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            $this->MODULE_ID,
            ApiShipHandler::class,
            'onSaleDeliveryHandlersClassNamesBuildList'
        );
        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleStatusOrderChange',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleStatusOrderChange'
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $context = \Bitrix\Main\Context::getCurrent();
        $request = $context->getRequest();
        Loader::includeModule($this->MODULE_ID);

        if ($request['step'] < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('BEERALEX_APISHIP_UNINSTALL_TITLE') ?: 'Удаление модуля',
                __DIR__ . '/unstep1.php'
            );
        } else {
            if ($request['savedata'] !== 'Y') {
                $this->UnInstallDB();
            }
            $this->UnInstallEvents();
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('BEERALEX_APISHIP_UNINSTALL_TITLE') ?: 'Удаление модуля',
                __DIR__ . '/unstep2.php'
            );
        }
    }
}
