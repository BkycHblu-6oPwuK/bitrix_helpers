import { createApp } from "vue";
import storeAbout from "@/store/about";

document.addEventListener('DOMContentLoaded', () => {
    // заинитим те vue приложения, что находятся не в bitrix компонентах
    // добавить в избранное
    document.querySelectorAll('.product-card-about').forEach((element) => {
        const productId = Number(element.dataset.id);
        const offerId = Number(element.dataset.offerid);
        const favouriteButton = element.querySelector('.vue-favourites');
        const dressingButton = element.querySelector('.vue-dressing');

        if (productId && favouriteButton) {
            window.vueApps.createFavouriteSmall(productId).mount(favouriteButton);
        }
        if(offerId && dressingButton) {
            window.vueApps.createDressingSmall(offerId).mount(dressingButton);
        }
    });
    // меню в шапке, для использования телепорта
    document.querySelectorAll('.header-main-menu').forEach((element) => {
        createApp().mount(element);
    })
    //поиск
    if (!storeAbout.getters.isMobile) {
        const headerSearch = document.getElementById('vue-header-search');
        headerSearch && window.vueApps.createSearchHeader().mount(headerSearch);
    } else {
        const headerMobileSearch = document.getElementById('vue-header-mobile-search');
        headerMobileSearch && window.vueApps.createSearchHeader().mount(headerMobileSearch);
    }
})