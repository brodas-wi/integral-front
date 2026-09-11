import Swiper from "swiper";
import "swiper/css";

export function initHeroPagesCarousels() {
    document.querySelectorAll(".hp-carousel").forEach((wrap) => {
        if (wrap.__hpSwiperInit) return;
        wrap.__hpSwiperInit = true;

        const swiperEl = wrap.querySelector(".hp-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".hp-nav-prev");
        const nextBtn = wrap.querySelector(".hp-nav-next");
        const paginationEl = wrap.querySelector(".hp-dots");

        const swiperInstance = new Swiper(swiperEl, {
            slidesPerView: 3,
            slidesPerGroup: 3,
            spaceBetween: 24,
            speed: 450,
            watchOverflow: true,
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
            breakpoints: {
                0: { slidesPerView: 1, slidesPerGroup: 1 },
                640: { slidesPerView: 2, slidesPerGroup: 2 },
                992: { slidesPerView: 3, slidesPerGroup: 3 },
            },
        });

        if (prevBtn) {
            prevBtn.addEventListener("click", () => swiperInstance.slidePrev());
        }
        if (nextBtn) {
            nextBtn.addEventListener("click", () => swiperInstance.slideNext());
        }
    });
}