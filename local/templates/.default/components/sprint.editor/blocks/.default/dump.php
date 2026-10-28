<?php
return;/**
 * блок для просмотра неизвестных блоков, скопируйте этот шаблон в
 * bitrix/templates/.default/components/sprint.editor/blocks/.default/dump.php
 * и выводите его содержание через echo '<pre>' . print_r($block, 1) . '</pre>';
 * или любым другим способом
 */
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
$GLOBALS['_CONTENT_BLOCK_DEPTH']--;
