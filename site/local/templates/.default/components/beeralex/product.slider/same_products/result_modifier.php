<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Content\ProductSliderDTO;

$arResult['DTO'] = ProductSliderDTO::make($arResult);
$this->getComponent()->setResultCacheKeys(['DTO']);