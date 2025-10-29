<? 
if(empty($arResult['items'])) return;
?>
<section class="section-slider">
    <div class="seasons__title-block">
        <h2 class="seasons__title"><?= $arResult['title'] ?></h2>
        <? if ($arResult['linkToAll']): ?>
            <a href="<?= $arResult['linkToAll'] ?>" class="seasons__link">Показать все</a>
        <? endif; ?>
    </div>
    <div class="seasons">
        <div class="seasons__container catalog-slider">
            <div class="seasons__container-wrapper swiper-wrapper">
                
                    <? foreach ($arResult['items'] as $item) {
                        $APPLICATION->IncludeComponent(
                            'bitrix:catalog.item',
                            'main-card',
                            [
                                'ITEM' => $item,
                                //'IS_BIG_CARD' => $item['id'] == $arResult['bigId'],
                                'VUE_CONTROLS' => true
                            ]
                        );
                    }
                    ?>
            </div>
        </div>
    </div>
</section>