<?php 
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spValue = htmlspecialcharsbx((string)$block['value']);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'textfield', 'content' => ['value' => $_spValue]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;