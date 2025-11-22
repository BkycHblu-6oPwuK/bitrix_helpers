<?php

use Beeralex\Api\ApiResult;

service(ApiResult::class)->addPageData([
    'items' => $arResult['ITEMS'],
    'pagination' => $arResult['PAGINATION'],
], 'catalogSection');