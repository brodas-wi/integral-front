const SELECTOR = '[data-open-form], a[href^="#form:"]';
const SLUG = /^[a-z0-9-]+$/;
const HASH = /^#form:([a-z0-9-]+)(?::([A-Za-z0-9_-]+))?$/;

let modulePromise = null;
const load = () => (modulePromise ??= import("./form-overlay.js"));

function readTrigger(element) {
    const prefill = {};
    let slug = element.getAttribute("data-open-form");

    if (slug === null) {
        const match = (element.getAttribute("href") || "").match(HASH);
        if (!match) return null;
        slug = match[1];
        if (match[2]) prefill.producto = match[2];
    }

    slug = slug.trim();
    if (!SLUG.test(slug)) return null;

    if (element.dataset.formProduct) prefill.producto = element.dataset.formProduct;

    if (element.dataset.formPrefill) {
        try {
            Object.assign(prefill, JSON.parse(element.dataset.formPrefill));
        } catch {
            /* ignorado: JSON inválido */
        }
    }

    return { slug, prefill };
}

function open(slug, prefill, trigger = null) {
    load()
        .then((module) => module.openForm(slug, { prefill, trigger }))
        .catch((error) => console.error("[Forms] No se pudo cargar el formulario:", error));
}

function openFromUrl() {
    const hash = location.hash.match(HASH);
    if (hash) {
        open(hash[1], hash[2] ? { producto: hash[2] } : {});
        return;
    }

    const params = new URLSearchParams(location.search);
    const slug = params.get("form");
    if (!slug || !SLUG.test(slug)) return;

    const prefill = {};
    params.forEach((value, key) => {
        if (key !== "form") prefill[key] = value;
    });
    open(slug, prefill);
}

export function initFormOverlay() {
    document.addEventListener("click", (event) => {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const element = event.target.closest(SELECTOR);
        if (!element) return;

        const trigger = readTrigger(element);
        if (!trigger) return;

        event.preventDefault();
        open(trigger.slug, trigger.prefill, element);
    });

    document.addEventListener(
        "pointerover",
        (event) => {
            if (event.target.closest(SELECTOR)) load().catch(() => {});
        },
        { passive: true },
    );

    openFromUrl();
}