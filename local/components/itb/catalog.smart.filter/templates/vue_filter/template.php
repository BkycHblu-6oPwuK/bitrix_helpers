<div class="filter">
    <div class="filter__block">

        <div class="filter__sort-mobile">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3 13.5H15M3 9H11.2494M3 4.5H8.25" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <span>Сортировка</span>
        </div>

        <div class="filter__price hover-filter-price">
            <span class="filter__price-title"><?=$arResult['VUE_DATA']['sorting']['title']?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M3.3335 15H16.6668M3.3335 10H12.4995M3.3335 5H9.16683" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <?
        foreach ($arResult['VUE_DATA']['items'] as $item):
        ?>
            <div class="filter__item hover-filter-item">
                <span class="filter__item-title"><?= $item['NAME'] ?></span>
                <span class="filter__item-num"></span>
                <div class="filter__item-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M5 7.5L10 12.5L15 7.5" stroke="#111827" stroke-width="1.5" stroke-linecap="square" />
                    </svg>
                </div>
            </div>
        <? endforeach; ?>
    </div>

    <div class="filter__clear"><span>Очистить фильтры</span></div>

    <div class="filter__mobile">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M11 6H3" stroke="#111827" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round" />
                <path d="M12.875 7.75C13.9105 7.75 14.75 6.91053 14.75 5.875C14.75 4.83947 13.9105 4 12.875 4C11.8395 4 11 4.83947 11 5.875C11 6.91053 11.8395 7.75 12.875 7.75Z" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M15 13L7 13" stroke="#111827" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round" />
                <path d="M4.875 14.75C5.91053 14.75 6.75 13.9105 6.75 12.875C6.75 11.8395 5.91053 11 4.875 11C3.83947 11 3 11.8395 3 12.875C3 13.9105 3.83947 14.75 4.875 14.75Z" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <span>Фильтр</span>
    </div>

</div>