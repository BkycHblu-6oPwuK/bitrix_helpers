<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spImage = Sprint\Editor\Blocks\Image::getImage(
    $block, [
        'width'  => 1024,
        'height' => 768,
        'exact'  => 0,
    ]
);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'image', 'content' => $_spImage ?: []];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
