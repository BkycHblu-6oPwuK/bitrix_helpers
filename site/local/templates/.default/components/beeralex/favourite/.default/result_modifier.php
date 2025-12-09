<?php

use Beeralex\Api\Domain\Favourite\FavouriteDTO;

$arResult['DTO'] = FavouriteDTO::make($arResult);

$this->getComponent()->setResultCacheKeys(['DTO']);