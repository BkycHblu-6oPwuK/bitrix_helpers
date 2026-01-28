<?php

use Beeralex\Catalog\EventHandlers;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class beeralex_catalog extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'beeralex.catalog';
        $this->MODULE_VERSION = '1.1.0';
        $this->MODULE_VERSION_DATE = '2025-04-09 12:00:00';
        $this->MODULE_NAME = 'beeralex.catalog';
        $this->MODULE_DESCRIPTION = 'beeralex.catalog module';
        $this->PARTNER_NAME = 'beeralex';
        $this->PARTNER_URI = '#';
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if ($this->checkRequirements()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException('Для установки модуля необходима версия главного модуля не ниже 14.00.00 и установленный beeralex.core');
        }
    }

    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'sale',
            'onSalePaySystemRestrictionsClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSalePaySystemRestrictionsClassNamesBuildList'
        );

        EventManager::getInstance()->registerEventHandler(
            'sale',
            'onSaleCashboxRestrictionsClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleCashboxRestrictionsClassNamesBuildList'
        );

        EventManager::getInstance()->registerEventHandler(
            'sale',
            'OnGetCustomCheckList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onGetCustomCheckList'
        );

        EventManager::getInstance()->registerEventHandler(
            'sale',
            'onSaleDeliveryExtraServicesClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleDeliveryExtraServicesClassNamesBuildList'
        );
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'OnSalePaySystemRestrictionsClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSalePaySystemRestrictionsClassNamesBuildList'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'OnSaleCashboxRestrictionsClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleCashboxRestrictionsClassNamesBuildList'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'OnGetCustomCheckList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onGetCustomCheckList'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'onSaleDeliveryExtraServicesClassNamesBuildList',
            $this->MODULE_ID,
            EventHandlers::class,
            'onSaleDeliveryExtraServicesClassNamesBuildList'
        );
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function checkRequirements(): bool
    {
        return version_compare(ModuleManager::getVersion('main'), '14.00.00') >= 0 && Loader::includeModule('beeralex.core');
    }
}
