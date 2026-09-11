import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

export function initHeroPagesCarousels() {
    document.querySelectorAll(".hp-carousel").forEach((wrap) => {
        if (wrap.__hpSwiperInit) return;
        wrap.__hpSwiperInit = true;

        const swiperEl = wrap.querySelector(".hp-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".hp-nav-prev");
        const nextBtn = wrap.querySelector(".hp-nav-next");
        const paginationEl = wrap.querySelector(".hp-dots");

        new Swiper(swiperEl, {
            modules: [Navigation, Pagination],
            slidesPerView: "auto",
            slidesPerGroupAuto: true,
            spaceBetween: 24,
            speed: 550,
            cssMode: false,
            watchOverflow: true,
            centeredSlidesBounds: true,
            navigation: {
                prevEl: prevBtn,
                nextEl: nextBtn,
                disabledClass: "hp-nav-disabled",
            },
            pagination: {
                el: paginationEl,
                clickable: true,
                bulletClass: "hp-dot",
                bulletActiveClass: "active",
            },
        });
    });
}