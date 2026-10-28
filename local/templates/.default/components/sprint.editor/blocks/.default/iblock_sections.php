<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;

$settings        = !empty($block['settings']) ? $block['settings'] : [];
$displayElemens  = !empty($settings['display_elements']);

if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    if ($displayElemens) {
        $_spItems = Sprint\Editor\Blocks\IblockSections::getElements($block);
        $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'iblock_elements', 'content' => [
            'items' => array_map(static fn($el) => ['id' => $el['ID'], 'name' => $el['NAME'], 'url' => $el['DETAIL_PAGE_URL']], $_spItems),
        ]];
    } else {
        $_spItems = Sprint\Editor\Blocks\IblockSections::getList($block);
        $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'iblock_sections', 'content' => [
            'items' => array_map(static fn($s) => ['id' => $s['ID'], 'name' => $s['NAME'], 'url' => $s['SECTION_PAGE_URL']], $_spItems),
        ]];
    }
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
