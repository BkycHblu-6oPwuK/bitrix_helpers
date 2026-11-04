<?php

use Beeralex\Api\EventHandlers;
use Bitrix\Main\EventManager;

require_once __DIR__ . '/../lib/EventHandlers.php';

global $APPLICATION;
if ($APPLICATION?->GetCurPage() === '/bitrix/admin/cache.php') {
    if (
        isset($_REQUEST["cachetype"])
        && isset($_REQUEST["clearcache"])
        && $_REQUEST["cachetype"] == "all" //Все
        && $_REQUEST["clearcache"] == "Y" //Очистка файлов кеша
    ) {
        register_shutdown_function(static function (): void {
            service(\Beeralex\Api\Domain\Services\CacheInvalidateService::class)->invalidateCache();
        });
    }
}

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler('main', 'OnPageStart', [
    EventHandlers::class,
    'onPageStart',
], false, 1);

// Элементы инфоблоков.
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', [
    EventHandlers::class,
    'onElementChange',
]);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', [
    EventHandlers::class,
    'onElementChange',
]);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', [
    EventHandlers::class,
    'onElementChange',
]);

// Разделы инфоблоков.
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionAdd', [
    EventHandlers::class,
    'onSectionChange',
]);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionUpdate', [
    EventHandlers::class,
    'onSectionChange',
]);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionDelete', [
    EventHandlers::class,
    'onSectionChange',
]);

// Сброс кеша после формирования буфера ответа.
$eventManager->addEventHandler('main', 'OnEndBufferContent', [
    EventHandlers::class,
    'onEndBufferContent',
]);
