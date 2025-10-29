<? if (false && !empty($arResult['types'])) : ?>
<div class="catalog-types">
    <?php foreach ($arResult['types'] as $type): ?>
        <div class="catalog-types__item">
            <a href="/<?= $type['TYPE'] ?>/">
                <img src="<?= $type['PICTURE_SRC'] ?>"
                    alt="<?= $type['NAME'] ?>">
                <div class="catalog-types__item-title">
                    <?= $type['NAME'] ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<? 
else :
    require $_SERVER['DOCUMENT_ROOT'] . '/include/mainPage.php';
endif;
?>