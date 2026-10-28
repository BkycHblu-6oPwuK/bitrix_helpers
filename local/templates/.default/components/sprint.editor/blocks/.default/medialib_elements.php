<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;

if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    if (empty($block['medialib_type']) || $block['medialib_type'] === 'image') {
        $_spImages = Sprint\Editor\Blocks\MedialibElements::getImages(
            $block,
            ['width' => 300, 'height' => 300, 'exact' => 0],
            ['width' => 1024, 'height' => 768, 'exact' => 0]
        );
        $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'gallery', 'content' => ['images' => array_values($_spImages ?: [])]];
    } else {
        $_spElements = Sprint\Editor\Blocks\MedialibElements::getElements($block);
        $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'iblock_elements', 'content' => [
            'items' => array_map(static fn($el) => ['id' => $el['ID'], 'name' => $el['NAME'], 'url' => $el['SRC']], $_spElements),
        ]];
    }
}

$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
?>