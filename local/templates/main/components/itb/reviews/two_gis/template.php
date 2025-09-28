<?php
if(!$arResult['isset_items']) return;
?>
<div class="about-reviews__container">
    <div class="about-reviews__block">
        <h2>Отзывы</h2>
        <div class="about-reviews__block-items js-review-items">
            <div class="swiper-wrapper">
                <? foreach ($arResult['items'] as $item): ?>
                    <div class="swiper-slide about-review">
                        <div class="about-review__source">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/images/about-page/2gis-logo.png" alt="">
                        </div>
                        <div class="about-review__head">
                            <div class="about-review__head-name">
                                <span class="span"><?= $item['user_name'] ?></span>
                                <span class="span"><?= $item['date'] ?> г.</span>
                            </div>
                            <div>
                                <? for ($i = 0; $i < $item['eval']; $i++): ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 0L14.6942 8.2918H23.4127L16.3593 13.4164L19.0534 21.7082L12 16.5836L4.94658 21.7082L7.64074 13.4164L0.587322 8.2918H9.30583L12 0Z" fill="#0F1523" />
                                    </svg>
                                <? endfor ?>
                                <? for ($i = 0; $i < 5 - $item['eval']; $i++): ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 0L14.6942 8.2918H23.4127L16.3593 13.4164L19.0534 21.7082L12 16.5836L4.94658 21.7082L7.64074 13.4164L0.587322 8.2918H9.30583L12 0Z" fill="#D1D5DB" />
                                    </svg>
                                <? endfor ?>
                            </div>
                        </div>
                        <div class="about-review__body">
                            <p><?= $item['review'] ?></p>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        </div>
    </div>
</div>