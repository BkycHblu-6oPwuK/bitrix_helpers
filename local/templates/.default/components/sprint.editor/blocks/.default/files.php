<?php
return;
/**
 * @var array $block
 * @var SprintEditorBlocksComponent $this
 */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $_spFiles = array_map(static function ($item) {
        return [
            'name' => (string)($item['file']['ORIGINAL_NAME'] ?? ''),
            'src'  => (string)($item['file']['SRC'] ?? ''),
            'desc' => (string)($item['desc'] ?? ''),
        ];
    }, $block['files'] ?? []);
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'files', 'content' => ['files' => $_spFiles]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
