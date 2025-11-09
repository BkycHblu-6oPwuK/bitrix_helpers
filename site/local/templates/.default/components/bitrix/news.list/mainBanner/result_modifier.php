<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Api\Domain\Iblock\ElementDTO;

$arResult['dto'] = new ContentItemDTO(
    ContentTypes::MAIN_BANNER,
    array_map([ElementDTO::class, 'fromNewsListElement'], $arResult['ITEMS'])
);

$this->getComponent()->setResultCacheKeys(['dto']);