<?php
/**
 *  @var \CBitrixComponentTemplate $this
 */
use Beeralex\Form\FormNewBuilder;

$arResult['VUE_DATA'] = (new FormNewBuilder($arResult))->build();

$this->getComponent()->setResultCacheKeys(['VUE_DATA']);