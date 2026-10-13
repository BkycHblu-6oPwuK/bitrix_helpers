<?php

declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Main\MainContentItemDTO;
use Beeralex\Api\Domain\Iblock\Main\Enum\MainContentTypes;
use Beeralex\Api\Domain\Iblock\Main\MainListArticlesDTO;

$arResult['DTO'] = MainContentItemDTO::makeFrom(
    MainContentTypes::ARTICLES,
    MainListArticlesDTO::make([
        'LINK' => $arParams['LINK_TO_ALL'] ?? '',
        'ITEMS' => $arResult['ITEMS'],
    ])
);

$this->getComponent()->setResultCacheKeys(['DTO']);
