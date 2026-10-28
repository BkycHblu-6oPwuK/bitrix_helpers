<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Beeralex\Api\Domain\Menu\MenuDTO;

/**
 * bitrix:menu отдаёт плоский список пунктов с DEPTH_LEVEL — собираем дерево
 * в формате MenuDTO (как у меню каталога): id, name, code, link, children.
 *
 * @var array $arResult
 */
$tree = [];
$parents = []; // DEPTH_LEVEL => ссылка на массив детей родителя этого уровня
$parents[1] = &$tree;

foreach ($arResult as $index => $item) {
    if (!is_array($item) || !isset($item['TEXT'])) {
        continue;
    }
    if (($item['PERMISSION'] ?? 'R') <= 'D') {
        continue;
    }

    $depth = max(1, (int)($item['DEPTH_LEVEL'] ?? 1));
    if (!isset($parents[$depth])) {
        continue;
    }

    $parents[$depth][] = [
        'ID' => $index + 1,
        'NAME' => $item['TEXT'],
        'CODE' => '',
        'IBLOCK_SECTION_ID' => 0,
        'LINK' => $item['LINK'],
        'CHILDREN' => [],
    ];
    $last = array_key_last($parents[$depth]);
    $parents[$depth + 1] = &$parents[$depth][$last]['CHILDREN'];
}
unset($parents);

$arResult = ['DTO' => MenuDTO::make(['MENU' => $tree])];

$this->getComponent()->setResultCacheKeys(['DTO']);
