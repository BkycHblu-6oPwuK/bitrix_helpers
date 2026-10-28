<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spElements = Sprint\Editor\Blocks\contents::getElements($block, $this);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'contents', 'content' => [
        'items' => array_map(static fn($el) => [
            'text'   => (string)($el['text'] ?? ''),
            'anchor' => (string)($el['anchor'] ?? ''),
            'level'  => (int)($el['level'] ?? 1),
        ], $_spElements),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
