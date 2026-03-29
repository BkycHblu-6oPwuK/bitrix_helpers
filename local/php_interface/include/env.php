<?php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/local/logs/error.log" );