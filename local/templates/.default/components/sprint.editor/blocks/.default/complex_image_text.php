<?php
return;
/**
 * @var array $block
 * @var SprintEditorBlocksComponent $this
 */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'complex_image_text', 'content' => [
        'image' => Sprint\Editor\Blocks\Image::getImage($block['image'] ?? [], ['width' => 1024, 'height' => 768, 'exact' => 0]) ?: [],
        'text'  => Sprint\Editor\Blocks\Text::getValue($block['text'] ?? []),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--; 
?>