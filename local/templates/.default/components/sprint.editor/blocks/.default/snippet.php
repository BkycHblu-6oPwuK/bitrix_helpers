<?php
return;
/** @var array $block */
/** @var SprintEditorBlocksComponent $this */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
Sprint\Editor\Blocks\Snippet::includeSnippet($block);
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;