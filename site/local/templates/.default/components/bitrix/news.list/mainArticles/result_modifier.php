<?php

declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Content\ListArticlesDTO;

$arResult['DTO'] = ContentItemDTO::makeFrom(
    MainContentTypes::ARTICLES,
    ListArticlesDTO::make([
        'LINK' => $arParams['LINK_TO_ALL'] ?? '',
        'ITEMS' => $arResult['ITEMS'],
    ])
);

$this->getComponent()->setResultCacheKeys(['DTO']);
