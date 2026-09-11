import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

const CARD_WIDTH = 260;
const CARD_GAP = 24;

function calcSlidesPerView(containerWidth) {
    const perView = Math.floor(
        (containerWidth + CARD_GAP) / (CARD_WIDTH + CARD_GAP),
    );
    return Math.max(1, perView);
}

function initCardHover(card) {
    if (!card || card.__hcInit) return;
    card.__hcInit = true;

    const video = card.querySelector(".hc-card-video");
    const shield = card.querySelector(".hc-card-video-shield");
    if (!video) return;

    video.muted = true;
    video.volume = 0;

    const isTouch = window.matchMedia("(hover: none)").matches;
    const hoverTarget = shield || card;

    if (!isTouch) {
        hoverTarget.addEventListener("mouseenter", () => {
            video.currentTime = 0;
            video.play().catch(() => { });
        });
        hoverTarget.addEventListener("mouseleave", () => {
            video.pause();
            video.currentTime = 0;
        });
    } else {
        card.addEventListener("click", (e) => {
            if (e.target.closest(".hc-card-btn")) return;
            if (video.paused) {
                video.currentTime = 0;
                video.play().catch(() => { });
            } else {
                video.pause();
                video.currentTime = 0;
            }
        });
    }
}

export function initHeroCardsCarousels() {
    document.querySelectorAll(".hc-carousel").forEach((wrap) => {
        if (wrap.__hcSwiperInit) return;
        wrap.__hcSwiperInit = true;

        const swiperEl = wrap.querySelector(".hc-swiper");
        if (!swiperEl) return;

        const prevBtn = wrap.querySelector(".hc-nav-prev");
        const nextBtn = wrap.querySelector(".hc-nav-next");
        const paginationEl = wrap.querySelector(".hc-dots");

        swiperEl.querySelectorAll(".hc-card").forEach(initCardHover);

        const initialPerView = calcSlidesPerView(swiperEl.offsetWidth);

        const swiperInstance = new Swiper(swiperEl, {
            modules: [Navigation, Pagination],
            slidesPerView: initialPerView,
            slidesPerGroup: initialPerView,
            spaceBetween: CARD_GAP,
            speed: 550,
            watchOverflow: true,
            centerInsufficientSlides: true,
            centeredSlides: false,
            navigation: {
                prevEl: prevBtn,
                nextEl: nextBtn,
                disabledClass: "hc-nav-disabled",
            },
            pagination: {
                el: paginationEl,
                clickable: true,
                bulletClass: "hc-dot",
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