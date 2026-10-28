<?php 
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spText = Sprint\Editor\Blocks\Text::getValue($block);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'text', 'content' => ['value' => $_spText]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>

