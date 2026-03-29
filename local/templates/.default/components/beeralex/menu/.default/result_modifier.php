<?php

use Beeralex\Api\Domain\Menu\MenuDTO;

$arResult['DTO'] = MenuDTO::make($arResult);

$this->getComponent()->setResultCacheKeys(['DTO']);