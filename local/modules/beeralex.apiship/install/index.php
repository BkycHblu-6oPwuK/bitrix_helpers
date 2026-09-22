<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class beeralex_apiship extends CModule
{
    /**
     * FQCN классов, создаваемых на поздних фазах разработки.
     * Держим строками, чтобы установка/удаление работали до появления самих классов.
     */
    private const ORDER_TABLE       = 'Beeralex\\Apiship\\Model\\OrderTable';
    private const STATUS_LOG_TABLE  = 'Beeralex\\Apiship\\Model\\StatusLogTable';
    private const WEBHOOK_LOG_TABLE = 'Beeralex\\Apiship\\Model\\WebhookLogTable';
    private const DELIVERY_HANDLER  = 'Beeralex\\Apiship\\Delivery\\ApiShipHandler';
    private const EVENT_HANDLERS    = 'Beeralex\\Apiship\\EventHandlers';
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
        foreach ([self::ORDER_TABLE, self::STATUS_LOG_TABLE, self::WEBHOOK_LOG_TABLE] as $table) {
            if (class_exists($table)) {
                $table::createTable();
            }
        }

        // UNIQUE INDEX обеспечивает идемпотентность вебхуков (ADR-007).
        $connection = \Bitrix\Main\Application::getConnection();
        if ($connection->isTableExists('beeralex_apiship_webhook_log')) {
            try {
                $connection->queryExecute(
                    'CREATE UNIQUE INDEX ix_apiship_webhook_event_id
                     ON beeralex_apiship_webhook_log (EVENT_ID)'
                );
            } catch (\Exception $e) {
                // индекс уже существует — норма при повторной установке
            }
        }
    }

    public function UnInstallDB()
    {
        foreach ([self::WEBHOOK_LOG_TABLE, self::STATUS_LOG_TABLE, self::ORDER_TABLE] as $table) {
            if (class_exists($table)) {
                $table::dropTable();
            }
        }
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        // Регистрация обработчика доставки в списке классов служб доставки.
        if (class_exists(self::DELIVERY_HANDLER)) {
            $eventManager->registerEventHandler(
                'sale',
                'onSaleDeliveryHandlersClassNamesBuildList',
                $this->MODULE_ID,
                self::DELIVERY_HANDLER,
                'onSaleDeliveryHandlersClassNamesBuildList'
            );
        }

        // Смена статуса заказа -> отправка заказа в ApiShip (момент задаётся в настройках).
        if (class_exists(self::EVENT_HANDLERS)) {
            $eventManager->registerEventHandler(
                'sale',
                'OnSaleStatusOrderChange',
                $this->MODULE_ID,
                self::EVENT_HANDLERS,
                'onSaleStatusOrderChange'
            );
        }
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            $this->MODULE_ID,
            self::DELIVERY_HANDLER,
            'onSaleDeliveryHandlersClassNamesBuildList'
        );

        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleStatusOrderChange',
            $this->MODULE_ID,
            self::EVENT_HANDLERS,
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
