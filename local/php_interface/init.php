<?
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";
require_once __DIR__ . '/include/env.php';
Loader::includeModule('beeralex.core');
require_once __DIR__ . "/include/functions.php";
require_once __DIR__ . "/include/events.php";

?>