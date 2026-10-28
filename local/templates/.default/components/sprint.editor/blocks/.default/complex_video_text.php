<?php
return;
/**
 * @var array $block
 * @var SprintEditorBlocksComponent $this
 */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $_spVideoBlock = $block['video'] ?? [];
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'complex_video_text', 'content' => [
        'video' => [
            'url'  => (string)($_spVideoBlock['url'] ?? ''),
            'id'   => (string)($_spVideoBlock['id'] ?? ''),
            'type' => (string)($_spVideoBlock['video_type'] ?? $_spVideoBlock['type'] ?? ''),
        ],
        'text' => Sprint\Editor\Blocks\Text::getValue($block['text'] ?? []),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--; 
?>