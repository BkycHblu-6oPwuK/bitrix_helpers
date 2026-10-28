<?php
/**
 * @var array $block
 * @var SprintEditorBlocksComponent $this
 */
return;
$GLOBALS['_CONTENT_BLOCK_DEPTH'] = ($GLOBALS['_CONTENT_BLOCK_DEPTH'] ?? 0) + 1;
if ($GLOBALS['_CONTENT_BLOCK_DEPTH'] === 1) {
    // Обработчик внутренних блоков через Sprint-классы, хранится в GLOBALS чтобы не дублироваться
    if (!isset($GLOBALS['_SP_PROCESS_INNER_BLOCK'])) {
        $GLOBALS['_SP_PROCESS_INNER_BLOCK'] = static function (array $b): array {
            $type = (string)($b['type'] ?? '');
            if (in_array($type, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                return ['type' => $type, 'content' => ['value' => (string)($b['value'] ?? ''), 'anchor' => (string)($b['anchor'] ?? '')]];
            }
            switch ($type) {
                case 'text':
                    return ['type' => 'text', 'content' => ['value' => Sprint\Editor\Blocks\Text::getValue($b)]];
                case 'image':
                    return ['type' => 'image', 'content' => Sprint\Editor\Blocks\Image::getImage($b, ['width' => 1024, 'height' => 768, 'exact' => 0]) ?: []];
                case 'video':
                    return ['type' => 'video', 'content' => ['url' => (string)($b['url'] ?? ''), 'id' => (string)($b['id'] ?? ''), 'type' => (string)($b['video_type'] ?? '')]];
                case 'lists':
                    $_tag = !empty($b['settings']['type']) ? $b['settings']['type'] : 'ul';
                    return ['type' => 'lists', 'content' => ['tag' => $_tag, 'value' => Sprint\Editor\Blocks\Lists::getValue($b, $_tag)]];
                case 'button_link':
                    return ['type' => 'button_link', 'content' => ['url' => (string)($b['url'] ?? ''), 'title' => (string)($b['title'] ?? ''), 'target' => (string)($b['target'] ?? '')]];
                default:
                    return ['type' => $type, 'content' => []];
            }
        };
    }
    $_spProcess = $GLOBALS['_SP_PROCESS_INNER_BLOCK'];
    $GLOBALS['_CONTENT_BLOCKS'][] = ['type' => 'accordion', 'content' => [
        'items' => array_map(static function ($item) use ($_spProcess) {
            return [
                'title'  => (string)($item['title'] ?? ''),
                'blocks' => array_map($_spProcess, $item['blocks'] ?? []),
            ];
        }, $block['items'] ?? []),
    ]];
}
$GLOBALS['_CONTENT_BLOCK_DEPTH']--; 
?>
