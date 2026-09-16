import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

export function initSplitCarousels() {
    document.querySelectorAll(".sc-carousel").forEach((wrap) => {
        if (wrap.__scSwiperInit) return;
        wrap.__scSwiperInit = true;

        const swiperEl = wrap.querySelector(".sc-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".sc-nav-prev");
        const nextBtn = wrap.querySelector(".sc-nav-next");
        const paginationEl = wrap.querySelector(".sc-dots");

        new Swiper(swiperEl, {
            modules: [Navigation, Pagination],
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 24,
            speed: 500,
            watchOverflow: true,
            observer: true,
            observeParents: true,
            resizeObserver: true,
            navigation: {
                prevEl: prevBtn,
                nextEl: nextBtn,
                disabledClass: "sc-nav-disabled",
            },
            pagination: {
                el: paginationEl,
                clickable: true,
                bulletClass: "sc-dot",
                bulletActiveClass: "active",
            },
            breakpoints: {
                480: { slidesPerView: 2, slidesPerGroup: 2 },
                992: { slidesPerView: 3, slidesPerGroup: 3 },
                1400: { slidesPerView: 4, slidesPerGroup: 4 },
            },
        });
    });
}