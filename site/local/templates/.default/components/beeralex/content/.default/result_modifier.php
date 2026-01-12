<?php

use Beeralex\Api\Domain\Iblock\Content\ContentDTO;

$arResult['DTO'] = ContentDTO::make($arResult);
$this->getComponent()->setResultCacheKeys(['DTO']);