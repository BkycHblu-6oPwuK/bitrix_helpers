<?php
declare(strict_types=1);

use Beeralex\Api\ApiResult;

$apiResult = service(ApiResult::class);
foreach ($arResult['BLOCKS'] ?? [] as $block) {
    $apiResult->addPageData($block);
}