<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Content\MainBannerDTO;

$arResult['DTO'] = ContentItemDTO::makeFrom(
    MainContentTypes::MAIN_BANNER,
    MainBannerDTO::make($arResult)
);

$this->getComponent()->setResultCacheKeys(['DTO']);