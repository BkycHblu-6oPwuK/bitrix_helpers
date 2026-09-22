<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Content\ProductSliderDTO;

$arResult['DTO'] = ContentItemDTO::makeFrom(
    MainContentTypes::PRODUCTS_SLIDER,
    ProductSliderDTO::make($arResult)
);
$this->getComponent()->setResultCacheKeys(['DTO']);