<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Form\FormNewBuilder;

$arResult['DTO'] = (new FormNewBuilder($arResult))->build();

$this->getComponent()->setResultCacheKeys(['DTO']);