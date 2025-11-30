<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Api\Domain\Iblock\Content\VideoDTO;

$arResult['DTO'] = ContentItemDTO::makeFrom(
    ContentTypes::VIDEO,
    VideoDTO::make($arResult)
);

$this->getComponent()->setResultCacheKeys(['DTO']);