<?php

use Bitrix\Main\Loader;

class beeralex_catalog extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'beeralex.catalog';
        $this->MODULE_VERSION = '1.0';
        $this->MODULE_VERSION_DATE = '2025-04-09 12:00:00';
        $this->MODULE_NAME = 'beeralex.catalog';
        $this->MODULE_DESCRIPTION = 'beeralex.catalog module';
        $this->PARTNER_NAME = 'beeralex';
        $this->PARTNER_URI = '#';
    }

    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
