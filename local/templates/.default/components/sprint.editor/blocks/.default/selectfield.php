<?php
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'selectfield', 'content' => ['value' => (string)($block['value'] ?? '')]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
