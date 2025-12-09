<?php

use Beeralex\Api\Domain\Menu\FavouriteDTO;

$arResult['DTO'] = FavouriteDTO::make($arResult);

$this->getComponent()->setResultCacheKeys(['DTO']);