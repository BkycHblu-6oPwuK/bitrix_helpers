<?
$localOptionsFileName = 'beeralex_notification_options.php';
$moduleDirPath = __DIR__;
$bitrixPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix';
$localPath = $_SERVER['DOCUMENT_ROOT'] . '/local';
$basePathToOptions = '/modules/beeralex.core/include/base_module_options.php';
if(file_exists($bitrixPath . $basePathToOptions)) {
    $beeralex_notification_default_option = include $bitrixPath . $basePathToOptions;
} elseif (file_exists($localPath . $basePathToOptions)) {
    $beeralex_notification_default_option = include $localPath . $basePathToOptions;
}