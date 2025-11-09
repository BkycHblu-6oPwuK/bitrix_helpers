<?php
declare(strict_types=1);

use Beeralex\Api\Domain\Form\FormNewBuilder;

$arResult['dto'] = (new FormNewBuilder($arResult, "/api/v1/web-form"))->build();

$this->getComponent()->setResultCacheKeys(['dto']);