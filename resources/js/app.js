// Integral Public — Base JS
import { initHeroPagesCarousels } from "./utils/hero-pages-carousel.js";
import { initHeroCardsCarousels } from "./utils/hero-cards-carousel.js";
import { initSplitCarousels } from "./utils/split-carousel.js";

// Smooth scroll
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    initHeroPagesCarousels();
    initHeroCardsCarousels();
    initSplitCarousels();
});