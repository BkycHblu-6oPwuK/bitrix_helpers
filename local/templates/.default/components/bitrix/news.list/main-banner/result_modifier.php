<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Main\MainContentItemDTO;
use Beeralex\Api\Domain\Iblock\Main\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Main\MainBannerDTO;

$arResult['DTO'] = MainContentItemDTO::makeFrom(
    MainContentTypes::MAIN_BANNER,
    MainBannerDTO::make($arResult)
);

$this->getComponent()->setResultCacheKeys(['DTO']);