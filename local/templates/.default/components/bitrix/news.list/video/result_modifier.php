<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Content\VideoDTO;

$arResult['DTO'] = ContentItemDTO::makeFrom(
    MainContentTypes::VIDEO,
    VideoDTO::make($arResult)
);

$this->getComponent()->setResultCacheKeys(['DTO']);