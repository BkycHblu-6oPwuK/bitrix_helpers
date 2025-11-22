<?php
declare(strict_types=1);

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;

if($arParams['IS_CONTENT_ACTION']) {
    $data = new ContentItemDTO(
    ContentTypes::FORM,
    (array) $arResult['dto']
    );
    service(ApiResult::class)->addPageData((array)$data);
} else {
    service(ApiResult::class)->addPageData((array) $arResult['dto'], 'form');
}



