<?

declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\ElementDTO;
use Beeralex\Api\Domain\Pagination\PaginationDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 */

$arResult['ITEMS'] = array_map([ElementDTO::class, 'fromNewsListElement'], $arResult['ITEMS']);
$arResult['PAGINATION'] = $arResult['NAV_RESULT'] instanceof \CIBlockResult
    ? PaginationDTO::fromResult($arResult['NAV_RESULT'])
    : null;

$this->getComponent()->setResultCacheKeys(['ITEMS', 'PAGINATION']);
