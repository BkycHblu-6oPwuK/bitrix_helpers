<?php

use Beeralex\Api\Domain\Iblock\SectionDTO;

$arResult['DTO'] = array_map([SectionDTO::class, 'make'], $arResult['SECTIONS'] ?? []);

$this->getComponent()->SetResultCacheKeys(['DTO']);