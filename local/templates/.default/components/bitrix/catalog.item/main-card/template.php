<?
/**
 * vue шаблон лежит в /local/js/vite/src/common/components, здесь vue не используется, но верстка должна совпадать
 */
$item = $arParams['ITEM'];
if($arParams['IS_BIG_CARD']){
    include __DIR__ . '/big_card.php';
} else {
    include __DIR__ . '/card.php';
}
?>

