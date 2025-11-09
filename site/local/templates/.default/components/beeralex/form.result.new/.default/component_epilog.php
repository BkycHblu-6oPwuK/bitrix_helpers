<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Api\GlobalResult;

if($arParams['IS_CONTENT_ACTION']) {
    $data = new ContentItemDTO(
    ContentTypes::FORM,
    (array) $arResult['dto']
    );
    GlobalResult::addPageData((array)$data);
} else {
    GlobalResult::$result['form'] = (array) $arResult['dto'];
}



