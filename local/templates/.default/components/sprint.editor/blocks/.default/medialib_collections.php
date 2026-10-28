<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spImages = Sprint\Editor\Blocks\MedialibCollections::getImages(
    $block,
    ['width' => 200, 'height' => 200, 'exact' => 0],
    ['width' => 1024, 'height' => 768, 'exact' => 0]
);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'gallery', 'content' => ['images' => array_values($_spImages ?: [])]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
