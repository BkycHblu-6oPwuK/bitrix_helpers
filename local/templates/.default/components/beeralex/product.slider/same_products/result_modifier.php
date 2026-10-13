<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\Catalog\ProductSliderDTO;

$arResult['DTO'] = ProductSliderDTO::make($arResult);
$this->getComponent()->setResultCacheKeys(['DTO']);