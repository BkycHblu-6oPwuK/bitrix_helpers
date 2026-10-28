<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'properties', 'content' => [
        'elements' => array_map(static fn($el) => [
            'title' => (string)($el['title'] ?? ''),
            'text'  => (string)($el['text'] ?? ''),
        ], $block['elements'] ?? []),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
