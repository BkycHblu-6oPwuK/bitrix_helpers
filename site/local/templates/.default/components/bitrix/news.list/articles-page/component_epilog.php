<?php
declare(strict_types=1);

global $APPLICATION;

use Beeralex\Api\ApiResult;

service(ApiResult::class)->setData([
    'articles' => $arResult['ITEMS'],
    'pagination' => $arResult['PAGINATION'],
]);