<?

declare(strict_types=1);

use Beeralex\Api\Domain\Iblock\ElementDTO;
use Beeralex\Api\Domain\Iblock\SectionItemsDTO;
use Beeralex\Api\Domain\Pagination\PaginationDTO;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 */
$arResult['DTO'] = SectionItemsDTO::makeFrom(array_map([ElementDTO::class, 'fromNewsListElement'], $arResult['ITEMS']), $arResult['NAV_RESULT'] ? PaginationDTO::fromResult($arResult['NAV_RESULT']) : null);

$this->getComponent()->setResultCacheKeys(['DTO']);
