<?php
return;
/**
 *  @var array $block
*/
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'button_link', 'content' => [
        'url'    => (string)($block['url'] ?? ''),
        'title'  => (string)($block['title'] ?? ''),
        'target' => (string)($block['target'] ?? ''),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
