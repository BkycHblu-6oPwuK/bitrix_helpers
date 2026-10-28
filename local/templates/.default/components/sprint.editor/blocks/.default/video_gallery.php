<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;

$_spItems = Sprint\Editor\Blocks\VideoGallery::getItems(
    $block, ['width' => 320, 'height' => 240, 'exact' => 0],
    ['width' => 1024, 'height' => 768, 'exact' => 0]
);

if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'video_gallery', 'content' => [
        'items' => array_map(static fn($item) => [
            'youtubeCode' => (string)($item['YOUTUBE_CODE'] ?? ''),
            'src'         => (string)($item['SRC'] ?? ''),
            'detailSrc'   => (string)($item['DETAIL_SRC'] ?? ''),
            'description' => (string)($item['DESCRIPTION'] ?? ''),
        ], $_spItems),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;