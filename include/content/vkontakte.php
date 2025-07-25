<?if(!$item) return;
?>
<section class="section-slider">
    <div class="vk-block">
        <div class="vk-block__title">
            <h2><?=$item['title']?></h2>
            <span>Следите за новинками и обзорами <br>в нашей группе VK</span>
        </div>
        <div class="vk-block__container">
            <div class="swiper-wrapper">
                <div class="vk-block__item swiper-slide">
					<img src="<?= SITE_TEMPLATE_PATH ?>/images/Rectangle 7.png" alt="">
                </div>
                <div class="vk-block__item swiper-slide">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/Rectangle 5.png" alt="">
                </div>
                <div class="vk-block__item swiper-slide">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/Rectangle 9.png" alt="">
                </div>
                <a href="<?=$item['link']?>" class="vk-block__item-main swiper-slide">
                    <div class="vk-block__item-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140"
                            fill="none">
                            <path d="M75.6962 110.48C32.1155 110.48 7.25791 80.6873 6.22217 31.1121H28.0523C28.7694 67.499 44.8629 82.9116 57.6105 86.0895V31.1121H78.1666V62.4936C90.7548 61.143 103.979 46.8427 108.44 31.1121H128.997C125.571 50.4972 111.23 64.7976 101.032 70.6767C111.23 75.4436 127.563 87.9169 133.778 110.48H111.15C106.29 95.385 94.1807 83.7061 78.1666 82.1172V110.48H75.6962Z"
                                fill="#6B7280" />
                        </svg>
                    </div>
                    <div class="vk-block__item-link">
                        <span class="span">Перейти в группу</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
                            fill="none">
                            <path d="M7.63642 19.3031L7.10609 19.8334L8.16675 20.8941L8.69708 20.3637L7.63642 19.3031ZM20.3637 8.69708C20.6566 8.40418 20.6566 7.92931 20.3637 7.63642C20.0709 7.34352 19.596 7.34352 19.3031 7.63642L20.3637 8.69708ZM8.69708 20.3637L20.3637 8.69708L19.3031 7.63642L7.63642 19.3031L8.69708 20.3637Z"
                                fill="#0F1523" />
                            <path d="M8.16675 8.16675H19.8334V19.8334" stroke="#0F1523" stroke-width="1.5"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                </a>
                <div class="vk-block__item swiper-slide">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/Rectangle 8.png" alt="">
                </div>
                <div class="vk-block__item swiper-slide">
					<img src="<?= SITE_TEMPLATE_PATH ?>/images/Rectangle 11.png" alt="">
                </div>
                <div class="vk-block__item swiper-slide">
					<img src="<?= SITE_TEMPLATE_PATH ?>/images/Фото-144 1.png" alt="">
                </div>
            </div>
        </div>
        <a href="https://vk.com/dzhavadoffshop" class="vk-block__link-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none">
                <path d="M21.6276 31.5652C9.17591 31.5652 2.07376 23.053 1.77783 8.88867H8.01502C8.21989 19.2849 12.818 23.6886 16.4602 24.5965V8.88867H22.3334V17.8548C25.93 17.469 29.7083 13.3831 30.9831 8.88867H36.8562C35.8774 14.4273 31.7799 18.5131 28.8662 20.1929C31.7799 21.5548 36.4467 25.1186 38.2223 31.5652H31.7572C30.3687 27.2524 26.9088 23.9155 22.3334 23.4616V31.5652H21.6276Z"
                    fill="#6B7280" />
            </svg>
            <span>Перейти в группу</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M4.89645 12.3964L4.54289 12.75L5.25 13.4571L5.60355 13.1036L4.89645 12.3964ZM13.1036 5.60355C13.2988 5.40829 13.2988 5.09171 13.1036 4.89645C12.9083 4.70118 12.5917 4.70118 12.3964 4.89645L13.1036 5.60355ZM5.60355 13.1036L13.1036 5.60355L12.3964 4.89645L4.89645 12.3964L5.60355 13.1036Z"
                    fill="#0F1523" />
                <path d="M5.25 5.25H12.75V12.75" stroke="#0F1523" stroke-linecap="square" />
            </svg>
        </a>
        <span class="vk-block__link-mobile-text">Следите за новинками и обзорами <br>в нашей группе VK</span>
    </div>
</section>