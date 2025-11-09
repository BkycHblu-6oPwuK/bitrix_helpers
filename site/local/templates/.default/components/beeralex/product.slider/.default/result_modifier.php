<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ContentItemDTO;
use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;

$arResult['dto'] = new ContentItemDTO(
    ContentTypes::SLIDER,
    $arResult
);
$this->getComponent()->setResultCacheKeys(['dto']);