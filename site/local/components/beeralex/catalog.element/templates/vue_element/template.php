<?

use Beeralex\Core\Helpers\LanguageHelper;

$price = $arResult['product']['preselectedOffer'] && $arResult['product']['preselectedOffer']['price']['priceValue'] ? $arResult['product']['preselectedOffer']['price'] : $arResult['product']['price'];
$reviewsText = "{$arResult['reviews']['eval_info']['countNumber']} " . LanguageHelper::getPlural($arResult['reviews']['eval_info']['countNumber'], ['отзыва', 'отзывов', 'отзывов']);
?>

<div id="catalog_element">
    <div class="section__container">
        <div class="product-card-details">
            <div class="product-card-details__name-block">
                <span class="product-card-details__name"><?= $arResult['product']['name'] ?? $arResult['product']['original_name'] ?></span>
                <div class="product-card-details__head">
                    <? if ($arResult['product']['model'] || $arResult['product']['article']): ?>
                        <div class="product-card-details__head-num">
                            <? if ($arResult['product']['model']): ?>
                                <span>Модель <?= $arResult['product']['model'] ?></span>
                            <?
                            endif;
                            if ($arResult['product']['article']):
                            ?>
                                <span>Артикул <?= $arResult['product']['article'] ?></span>
                            <? endif; ?>
                        </div>
                    <? endif; ?>
                    <div class="product-card-details__head-stars">
                        <? for ($i = 0; $i < 5; $i++): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M12 0L14.6942 8.2918H23.4127L16.3593 13.4164L19.0534 21.7082L12 16.5836L4.94658 21.7082L7.64074 13.4164L0.587322 8.2918H9.30583L12 0Z"
                                    fill="#D1D5DB" />
                            </svg>
                        <? endfor; ?>
                    </div>
                    <a class="product-card-details__head-reviews"><?= $arResult['reviews']['eval_info']['count'] ?></a>
                </div>
            </div>
            <div class="product-card-details__photo-block">
                <div class="product-card-details__photo-block-sticky">
                    <div class="product-card-details__photo-container">
                        <div class="swiper-wrapper">
                            <? foreach ([$arResult['product']['imageSrc'], ...$arResult['product']['morePhoto'] ?? []] as $photo): ?>
                                <div class="product-card-details__photo swiper-slide">
                                    <img src="<?= $photo ?>">
                                </div>
                            <? endforeach; ?>
                        </div>
                    </div>
                    <span class="product-card-details__photo-info"></span>
                </div>
            </div>
            <div class="product-card-details__price-block">
                <div class="product-card-details__price">
                    <span class="product-card-details__price-result"><?= $price['priceFormatted'] ?> ₽</span>
                    <? if ($price['discountPercent']): ?>
                        <span class="product-card-details__price-current"><?= $price['oldPriceFormatted'] ?> ₽</span>
                        <span class="product-card-details__price-discount">-<?= $price['discountPercent'] ?>%</span>
                    <? endif; ?>
                </div>
            </div>
            <? if (!empty($arResult['colors'])): ?>
                <div class="product-card-details__color-block">
                    <span class="product-card-details__color-title">Выбор цвета</span>
                    <div class="product-card-details__color-container">
                        <? foreach ($arResult['colors'] as $color): ?>
                            <div class="product-card-details__color product-card-details__color">
                                <div><img src="<?= $color['file'] ?>"></div>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            <? endif; ?>
            <? if (!empty($arResult['product']['offers'])): ?>
                <div class="product-card-details__size-block">
                    <div class="product-card-details__size-title-block">
                        <span>Выбор размера</span>
                        <a href="#">Таблица размеров</a>
                    </div>
                    <div class="product-card-details__size-container">
                        <div class="swiper-wrapper">
                            <? foreach ($arResult['product']['offers'] as $offer): ?>
                                <div class="product-card-details__size swiper-slide">
                                    <span><?= $offer['razmer'] ?></span>
                                </div>
                            <? endforeach; ?>
                        </div>
                    </div>
                    <a href="/sizing/" class="product-card-details__size-link-mobile">Таблица размеров</a>
                    <div class="product-card-details__description-mobile">
                        <? if ($arResult['propertiesDefault']['RAZMER_MODELI_NA_FOTO']): ?>
                            <span>Размер на модели: <?= $arResult['propertiesDefault']['RAZMER_MODELI_NA_FOTO']['VALUE'] ?> RUS.</span>
                        <? endif; ?>
                        <? if ($arResult['propertiesDefault']['RAZMER_MODELI_NA_FOTO']): ?>
                            <span>Параметры модели: рост <?= $arResult['propertiesDefault']['ROST_MODELI_NA_FOTO']['VALUE'] ?> см</span>
                        <? endif; ?>
                    </div>
                </div>
            <? endif; ?>
            <div class="product-card-details__btn-container">
                <button type="submit" class="product-card-details__btn-cart">Добавить в корзину</button>
                <div class="product-card-details__btn-block">
                    <button class="product-card-details__btn-dressing">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                            <path
                                d="M11.9572 11.8658L11.5721 11.2223L11.5559 11.232L11.5402 11.2425L11.9572 11.8658ZM13.5428 11.8658L13.9598 11.2425L13.9441 11.232L13.928 11.2223L13.5428 11.8658ZM22.0318 17.5457L21.6148 18.169L21.6304 18.1795L21.6466 18.1892L22.0318 17.5457ZM3.46817 17.5457L3.85336 18.1892L3.86955 18.1795L3.88523 18.169L3.46817 17.5457ZM13.8379 8.59412L14.1658 9.26864H14.1658L13.8379 8.59412ZM9.26627 6.30715C9.26627 6.72136 9.60206 7.05715 10.0163 7.05715C10.4305 7.05715 10.7663 6.72136 10.7663 6.30715H9.26627ZM12.6557 11.6506L12.7011 12.3992L12.6557 11.6506ZM21.2391 19.5H4.26092V21H21.2391V19.5ZM3.88523 18.169L12.3743 12.4892L11.5402 11.2425L3.0511 16.9223L3.88523 18.169ZM13.1257 12.4892L21.6148 18.169L22.4489 16.9223L13.9598 11.2425L13.1257 12.4892ZM21.2391 21C23.4614 21 24.4124 18.0965 22.417 16.9021L21.6466 18.1892C22.2365 18.5423 22.0556 19.5 21.2391 19.5V21ZM4.26092 19.5C3.4444 19.5 3.26351 18.5423 3.85336 18.1892L3.08298 16.9021C1.08761 18.0965 2.03862 21 4.26092 21V19.5ZM14.5452 6.30715C14.5452 7.00035 14.1347 7.61592 13.51 7.91959L14.1658 9.26864C15.2695 8.73213 16.0452 7.61503 16.0452 6.30715H14.5452ZM10.7663 6.30715C10.7663 5.33131 11.5896 4.5 12.6557 4.5V3C10.8064 3 9.26627 4.45844 9.26627 6.30715H10.7663ZM12.6557 4.5C13.7218 4.5 14.5452 5.33131 14.5452 6.30715H16.0452C16.0452 4.45844 14.5051 3 12.6557 3V4.5ZM13.51 7.91959C13.1468 8.09614 12.7633 8.34948 12.4612 8.68695C12.1558 9.02803 11.9057 9.48947 11.9057 10.0515H13.4057C13.4057 9.95791 13.4443 9.83767 13.5787 9.68748C13.7164 9.53368 13.924 9.38615 14.1658 9.26864L13.51 7.91959ZM11.9057 10.0515V11.6506H13.4057V10.0515H11.9057ZM12.3424 12.5094C12.4516 12.444 12.5747 12.4068 12.7011 12.3992L12.6104 10.9019C12.2505 10.9237 11.8932 11.0301 11.5721 11.2223L12.3424 12.5094ZM12.7011 12.3992C12.8605 12.3895 13.0202 12.4271 13.1576 12.5094L13.928 11.2223C13.5239 10.9804 13.0627 10.8745 12.6104 10.9019L12.7011 12.3992Z"
                                fill="#28313D" />
                        </svg>
                        <span>В примерочную</span>
                    </button>
                    <button class="product-card-details__btn-favourite">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12.25 6.50019C10.4506 4.40317 7.44377 3.7551 5.18923 5.67534C2.93468 7.59558 2.61727 10.8061 4.38778 13.0772C5.85984 14.9654 10.3148 18.9479 11.7749 20.2369C11.9382 20.3811 12.0199 20.4532 12.1152 20.4815C12.1983 20.5062 12.2893 20.5062 12.3725 20.4815C12.4678 20.4532 12.5494 20.3811 12.7128 20.2369C14.1729 18.9479 18.6278 14.9654 20.0999 13.0772C21.8704 10.8061 21.5917 7.57538 19.2984 5.67534C17.0051 3.7753 14.0494 4.40317 12.25 6.50019Z"
                                stroke="#28313D" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                        <span>В избранное</span>
                    </button>
                </div>
            </div>
            <div class="product-card-details__delivery-container">
                <?/*
                <div class="product-card-details__delivery-item">
                    <span class="product-card-details__delivery-item-title">Курьерская доставка</span>
                    <span class="product-card-details__delivery-item-date">Сегодня</span>
                </div>
                */
                if ($arResult['delivery']['sdekDate']):
                ?>
                    <div class="product-card-details__delivery-item">
                        <span class="product-card-details__delivery-item-title">Доставка в ПВЗ</span>
                        <span class="product-card-details__delivery-item-date"><?= $arResult['delivery']['sdekDate'] ?></span>
                    </div>
                <? endif; ?>
                <a href="/buyers/how-to-buy/" class="product-card-details__delivery-link">Условия покупки</a>
                <a href="/buyers/guarantees/" class="product-card-details__delivery-link">Гарантия</a>
            </div>
            <? if (!empty($arResult['product']['detailText'])): ?>
                <div class="product-card-details__description-container">
                    <div class="product-card-details__description-title">
                        <span>Описание</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
                            <path ref="svg" d="M7.99984 3.16699V13.8337M13.3332 8.50033L2.6665 8.50032" stroke="#0F1523"
                                stroke-width="1.5" stroke-linecap="square" />
                        </svg>
                    </div>
                    <p><?= $arResult['product']['detailText'] ?></p>
                </div>
            <? endif; ?>
            <? if (!empty($arResult['properties'])): ?>
                <div class="product-card-details__specs-container">
                    <span class="product-card-details__specs-title">Характеристики</span>
                    <div class="product-card-details__specs-block">
                        <? foreach ($arResult['properties'] as $prop): ?>
                            <div class="product-card-details__specs-item">
                                <span class="product-card-details__specs-item-title"><?= $prop['name'] ?></span>
                                <span class="product-card-details__specs-item-value"><?= $prop['value'] ?></span>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            <? endif; ?>
            <div class="product-card-details__fixed-menu">
                <div class="product-card-details__mobile-price">
                    <span class="product-card-details__mobile-price-result"><?= $price['priceFormatted'] ?> ₽</span>
                    <? if ($price['discountPercent']): ?>
                        <span class="product-card-details__mobile-price-current"><?= $price['oldPriceFormatted'] ?> ₽</span>
                        <span class="product-card-details__mobile-price-discount">-<?= $price['discountPercent'] ?>%</span>
                    <? endif; ?>
                </div>
                <span class="product-card-details__mobile-price-credit">от 15 000 ₽ в месяц</span>
                <button type="submit" class="product-card-details__mobile-btn-cart">Добавить в корзину</button>
            </div>
        </div>
    </div>
    <section class="section-slider">
        <div class="reviews">
            <div class="reviews__title-block">
                <h2 class="reviews__title">Отзывы о товаре</h2>
            </div>
            <div class="reviews__block">
                <div class="reviews__rating-mobile">
                    <span class="reviews__rating-mobile-num"><?= $arResult['reviews']['eval_info']['avg'] ?></span>
                    <span class="reviews__rating-mobile-text">На основе <?= $reviewsText ?></span>
                    <button class="reviews__rating-mobile-btn">Оставить отзыв</button>
                </div>
                <div class="reviews__container">
                    <div class="swiper-wrapper">
                        <div class="reviews__rating swiper-slide">
                            <span class="reviews__rating-num"><?= $arResult['reviews']['eval_info']['avg'] ?></span>
                            <span class="reviews__rating-text">На основе <?= $reviewsText ?></span>
                            <button class="reviews__rating-btn">Оставить отзыв</button>
                        </div>
                        <? foreach ($arResult['reviews']['items'] as $review): ?>
                            <div class="reviews__item swiper-slide">
                                <div class="reviews__item-head">
                                    <div class="reviews__item-head-author">
                                        <span class="reviews__item-head-author-name"><?= $review['user_name'] ?></span>
                                        <span class="reviews__item-head-author-date"><?= $review['date'] ?> г.</span>
                                    </div>
                                    <div class="reviews__item-head-stars"></div>
                                </div>
                                <p class="reviews__item-text"></p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const jsonData = <?= $arResult['js_data'] ?>;
        window.vueApps.createCatalogElement(jsonData).mount('#catalog_element');
    })
</script>