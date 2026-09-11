import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

const CARD_WIDTH = 280;
const CARD_GAP = 24;

function calcSlidesPerView(containerWidth) {
    const perView = Math.floor(
        (containerWidth + CARD_GAP) / (CARD_WIDTH + CARD_GAP),
    );
    return Math.max(1, perView);
}

export function initHeroPagesCarousels() {
    document.querySelectorAll(".hp-carousel").forEach((wrap) => {
        if (wrap.__hpSwiperInit) return;
        wrap.__hpSwiperInit = true;

        const swiperEl = wrap.querySelector(".hp-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".hp-nav-prev");
        const nextBtn = wrap.querySelector(".hp-nav-next");
        const paginationEl = wrap.querySelector(".hp-dots");

        const initialPerView = calcSlidesPerView(swiperEl.offsetWidth);

        const swiperInstance = new Swiper(swiperEl, {
            modules: [Navigation, Pagination],
            slidesPerView: initialPerView,
            slidesPerGroup: initialPerView,
            spaceBetween: CARD_GAP,
            speed: 550,
            watchOverflow: true,
            centerInsufficientSlides: true,
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

        let lastPerView = initialPerView;
        let resizeTimer = null;

        window.addEventListener("resize", () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const newPerView = calcSlidesPerView(swiperEl.offsetWidth);
                if (newPerView !== lastPerView) {
                    lastPerView = newPerView;
                    swiperInstance.params.slidesPerView = newPerView;
                    swiperInstance.params.slidesPerGroup = newPerView;
                    swiperInstance.update();
                }
            }, 200);
        });
    });
}