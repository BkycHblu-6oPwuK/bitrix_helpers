<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'video', 'content' => [
        'url'   => (string)($block['url'] ?? ''),
        'id'    => (string)($block['id'] ?? ''),
        'type'  => (string)($block['video_type'] ?? $block['type'] ?? ''),
    ]];
}
$_spVideoHtml = Sprint\Editor\Blocks\Video::getHtml($block);
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>