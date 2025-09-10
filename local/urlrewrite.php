<?
/**
 * для версии модуля main >= 23.500.0
 * +стандартный routing_index подключает urlrewrite, но чпу не работают, здесь это решено
 * +стандартный routing_index не запускает контроллеры роута если они не в модуле, здесь можно размещать контроллеры вне модулей
 */
include_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/routing_index.php');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/404.php'))
	include_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');


