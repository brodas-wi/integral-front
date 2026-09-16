import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

const CARD_WIDTH = 260;
const CARD_GAP = 24;

function calcSlidesPerView(containerWidth) {
    if (containerWidth < CARD_WIDTH + CARD_GAP) {
        return 1;
    }
    const perView = Math.floor(
        (containerWidth + CARD_GAP) / (CARD_WIDTH + CARD_GAP),
    );
    return Math.max(1, perView);
}

export function initSplitCarousels() {
    document.querySelectorAll(".sc-carousel").forEach((wrap) => {
        if (wrap.__scSwiperInit) return;
        wrap.__scSwiperInit = true;

        const swiperEl = wrap.querySelector(".sc-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".sc-nav-prev");
        const nextBtn = wrap.querySelector(".sc-nav-next");
        const paginationEl = wrap.querySelector(".sc-dots");

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
                disabledClass: "sc-nav-disabled",
            },
            pagination: {
                el: paginationEl,
                clickable: true,
                bulletClass: "sc-dot",
                bulletActiveClass: "active",
            },
        });

        let lastPerView = initialPerView;
        let resizeTimer = null;

        const recalc = () => {
            const newPerView = calcSlidesPerView(swiperEl.offsetWidth);
            if (newPerView !== lastPerView) {
                lastPerView = newPerView;
                swiperInstance.params.slidesPerView = newPerView;
                swiperInstance.params.slidesPerGroup = newPerView;
                swiperInstance.update();
            }
        };

        if (typeof ResizeObserver !== "undefined") {
            const observer = new ResizeObserver(() => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(recalc, 150);
            });
            observer.observe(swiperEl);
        } else {
            window.addEventListener("resize", () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(recalc, 200);
            });
        }
    });
}