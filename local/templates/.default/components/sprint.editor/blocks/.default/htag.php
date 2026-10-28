<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */

$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;

$tag = in_array($block['type'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $block['type'] : 'h2';

$anc = !empty($block['anchor']) ? '<a name="' . htmlspecialcharsbx($block['anchor']) . '"></a>' : '';

$val = htmlspecialcharsbx((string)$block['value']);

if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => $tag, 'content' => ['value' => (string)$block['value'], 'anchor' => $block['anchor'] ?? '']];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;