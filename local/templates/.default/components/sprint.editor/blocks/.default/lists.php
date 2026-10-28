<?php 
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spSettings = !empty($block['settings']) ? $block['settings'] : [];
$_spListTag  = !empty($_spSettings['type']) ? $_spSettings['type'] : 'ul';
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'lists', 'content' => [
        'tag'   => $_spListTag,
        'value' => Sprint\Editor\Blocks\Lists::getValue($block, $_spListTag),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>