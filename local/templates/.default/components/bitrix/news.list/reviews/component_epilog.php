<?php
declare(strict_types=1);

global $APPLICATION;

use Beeralex\Api\ApiResult;

$apiResult = service(ApiResult::class);

$apiResult->addPageData($arResult['DTO'], 'section');
$apiResult->addPageData($arResult['FILTER_DTO'], 'filter');