<?php

use Beeralex\Api\Domain\Iblock\Catalog\SectionsDTO;

$arResult['DTO'] = array_map([SectionsDTO::class, 'make'], $arResult['SECTIONS'] ?? []);

$this->getComponent()->SetResultCacheKeys(['DTO']);