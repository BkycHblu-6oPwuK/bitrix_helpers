<?
use Bitrix\Main\Loader;
require_once __DIR__ . '/include/env.php';
Loader::includeModule('beeralex.core');
Loader::includeModule('beeralex.oauth2');
Loader::includeModule('beeralex.notification');
Loader::includeModule('beeralex.user');
Loader::includeModule('beeralex.api');
require_once __DIR__ . "/include/functions.php";
require_once __DIR__ . "/include/events.php";
?>