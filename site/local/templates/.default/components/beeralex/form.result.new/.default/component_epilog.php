<?php
declare(strict_types=1);

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;

if($arParams['IS_CONTENT_ACTION']) {
    $data = ContentItemDTO::makeFrom(ContentTypes::FORM, $arResult['DTO']);
    service(ApiResult::class)->addPageData($data);
} else {
    service(ApiResult::class)->addPageData($arResult['DTO'], 'form');
}



