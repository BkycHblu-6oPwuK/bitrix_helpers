<?php

use App\EventHandlers\Iblock;
use App\EventHandlers\Buffer;
use App\EventHandlers\Mail;
use App\EventHandlers\Main;
use App\EventHandlers\Sale;

\Bitrix\Main\Loader::includeModule('sale');

$eventManager = \Bitrix\Main\EventManager::getInstance();

// Buffer
$eventManager->addEventHandler('main', 'OnEndBufferContent', [Buffer::class, 'onEndBufferContent']);
$eventManager->addEventHandler('main', 'OnPageStart', [Main::class, 'onPageStart']);

// iblock
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', [Iblock::class, 'onAfterIBlockElementSetPropertyValues']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', [Iblock::class, 'onAfterIBlockElementSetPropertyValuesEx']);
//$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', [Iblock::class, 'onBeforeIBlockElementAdd']);
//$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [Iblock::class, 'onBeforeIBlockElementUpdate']);
//$eventManager->addEventHandler('iblock', 'OnBeforeIBlockPropertyUpdate', [Iblock::class, 'onBeforeIBlockPropertyUpdate']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockSectionUpdate', [Iblock::class, 'onBeforeIBlockSectionUpdate']);

// Sale
$eventManager->addEventHandler('sale', 'OnSaleOrderBeforeSaved', [Sale::class, 'OnSaleOrderBeforeSaved']);
$eventManager->addEventHandler(
	'sale',
	'onSaleDeliveryExtraServicesClassNamesBuildList',
	[Sale::class, 'onSaleDeliveryExtraServicesClassNamesBuildList']
);

//mail
$eventManager->addEventHandler('main', '\Bitrix\Main\Mail\Internal\Event::OnBeforeAdd', [Mail::class, 'onBeforeAdd']);
$eventManager->addEventHandler('main', 'OnBeforeEventSend', [Mail::class, 'OnBeforeEventSend']);