<?php

use Bitrix\Main\Loader;

class beeralex_content extends CModule
{
    var $MODULE_ID = 'beeralex.content';
    var $MODULE_NAME = 'beeralex.content';
    var $MODULE_DESCRIPTION = "beeralex.content";
    var $MODULE_VERSION = "1.0";
    var $MODULE_VERSION_DATE = "2024-04-09 12:00:00";
    var $PARTNER_NAME = 'beeralex';
    var $PARTNER_URI = '#';

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
