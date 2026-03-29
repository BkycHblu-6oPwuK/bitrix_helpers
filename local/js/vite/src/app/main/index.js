import Swiper from "swiper";
import { Navigation, Pagination, Autoplay } from "swiper/modules";
import './banner.scss';
import './index.scss';

document.addEventListener("DOMContentLoaded", () => {
    new Swiper(".banner__container", {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: {
            delay: 5000,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
});