// Integral Public — Base JS
import { initHeroPagesCarousels } from "./utils/hero-pages-carousel.js";
import { initHeroCardsCarousels } from "./utils/hero-cards-carousel.js";
import { initSplitCarousels } from "./utils/split-carousel.js";
import { initHeroVideoMuteButtons } from "./utils/hero-video.js";
import { initFormOverlay } from "./forms/overlay.js";

// Smooth scroll
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        const href = anchor.getAttribute('href');
        if (href === '#' || href.startsWith('#form:')) return;
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    initHeroPagesCarousels();
    initHeroCardsCarousels();
    initSplitCarousels();
    initHeroVideoMuteButtons();
    initFormOverlay();
});