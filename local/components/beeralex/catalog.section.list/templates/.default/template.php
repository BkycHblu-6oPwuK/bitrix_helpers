<?php 
if(!empty($arResult['sections'])): ?>
    <section class="catalog-sections">
        <div class="sections-slider js-catalog-sections">
            <div class="swiper-wrapper">

                <? foreach ($arResult['sections'] as &$arSection): ?>
                    <a class="swiper-slide sections-slider__item" href="<?= $arSection['url']; ?>">
                        <div class="sections-slider__item-image">
                            <img src="<?=$arSection['picture'] ?>" alt="">
                        </div>
                        <div class="sections-slider__item-title">
                            <?= $arSection['name']; ?>
                        </div>
                    </a>
                <? endforeach; ?>

            </div>
        </div>
    </section>
<?php endif ?>