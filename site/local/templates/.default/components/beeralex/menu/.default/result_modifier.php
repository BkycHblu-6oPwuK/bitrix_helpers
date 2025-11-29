<?php

$arResult['DTO'] = \Beeralex\Api\Domain\Menu\MenuDTO::make($arResult);

$this->getComponent()->setResultCacheKeys(['DTO']);