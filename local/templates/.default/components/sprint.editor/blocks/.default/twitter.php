<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'twitter', 'content' => [
        'html' => Sprint\Editor\Blocks\Twitter::getHtml($block),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
