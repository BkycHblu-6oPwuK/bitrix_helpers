<?php
return;
/**
 * @var array $block
 * @var SprintEditorBlocksComponent $this
 */
use Sprint\Editor\Blocks\FlickrPhotoset;

$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$_spImages = FlickrPhotoset::getPhotos($block['photoset_id']);
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'gallery', 'content' => [
        'images' => array_values($_spImages ?: []),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>
