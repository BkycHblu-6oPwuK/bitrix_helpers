<?
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";
require_once __DIR__ . '/include/env.php';
Loader::includeModule('itb.core');
require_once __DIR__ . '/include/injection.php';
require_once __DIR__ . "/include/functions.php";
require_once __DIR__ . "/include/events.php";

?>