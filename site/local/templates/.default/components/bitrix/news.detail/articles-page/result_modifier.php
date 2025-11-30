<?
declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\ElementDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 */

$arResult['DTO'] = ElementDTO::fromNewsDetailElement($arResult);
$this->getComponent()->setResultCacheKeys(['DTO']);