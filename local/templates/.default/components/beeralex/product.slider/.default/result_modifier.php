<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Main\MainContentItemDTO;
use Beeralex\Api\Domain\Iblock\Main\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Catalog\ProductSliderDTO;

$arResult['DTO'] = MainContentItemDTO::makeFrom(
    MainContentTypes::PRODUCTS_SLIDER,
    ProductSliderDTO::make($arResult)
);
$this->getComponent()->setResultCacheKeys(['DTO']);