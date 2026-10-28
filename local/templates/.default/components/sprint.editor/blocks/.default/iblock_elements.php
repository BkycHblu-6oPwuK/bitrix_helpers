<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
use Beeralex\Catalog\Enum\DIServiceKey;

$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;

if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $elements  = Sprint\Editor\Blocks\IblockElements::getList($block);
    $iblockId  = (int)($block['iblock_id'] ?? 0);
    $catalogId = (int)(service(DIServiceKey::PRODUCT_REPOSITORY->value)->entityId ?? 0);

    if ($iblockId && $iblockId === $catalogId) {
        // Продуктовый инфоблок — подключаем слайдер через компонент
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            'beeralex:product.slider',
            'content',
            ['IDS' => array_column($elements, 'ID'), 'TITLE' => (string)($block['title'] ?? ''), 'CACHE_TYPE' => 'A', 'CACHE_TIME' => '3600000'],
            false
        );
    } else {
        $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'iblock_elements', 'content' => [
            'items' => array_map(static fn($el) => ['id' => $el['ID'], 'name' => $el['NAME'], 'url' => $el['DETAIL_PAGE_URL']], $elements),
        ]];
    }
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
