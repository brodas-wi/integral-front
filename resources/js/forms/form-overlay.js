import "../../css/form-overlay.css";
import { initialValue, isEmpty, sanitize, validate } from "./form-rules.js";

const meta = (name, fallback) => document.querySelector(`meta[name="${name}"]`)?.content || fallback;
const formsUrl = () => meta("api-forms-url", "/api/forms").replace(/\/$/, "");
const geographyUrl = () => meta("api-geography-url", "/api/geography");

const INPUT_MODES = {
    dui: "numeric",
    nit: "numeric",
    phone_sv: "tel",
    phone_intl: "tel",
    email: "email",
    integer: "numeric",
    decimal: "decimal",
};

const AUTOCOMPLETE = {
    proper_name: "name",
    phone_sv: "tel",
    phone_intl: "tel",
    email: "email",
};

let geography = null;
let active = null;
let counter = 0;

function h(tag, attrs = {}, ...children) {
    const element = document.createElement(tag);
    for (const [name, value] of Object.entries(attrs)) {
        if (value == null || value === false) continue;
        if (name === "text") element.textContent = value;
        else element.setAttribute(name, value === true ? "" : value);
    }
    element.append(...children.filter(Boolean));
    return element;
}

async function getJson(url) {
    const response = await fetch(url, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
    });
    if (!response.ok) {
        const error = new Error(String(response.status));
        error.status = response.status;
        throw error;
    }
    return response.json();
}

async function loadGeography() {
    geography ??= await getJson(geographyUrl());
    return geography;
}

const isSafeRedirect = (url) => /^\/(?!\/)/.test(url) || /^https?:\/\//i.test(url);

/* ---------- Controles ---------- */

function buildText(field, id, notify) {
    const multiline = field.type === "textarea";
    const hasLimit = ["text", "proper_name", "textarea"].includes(field.type) && field.max_length;

    const input = h(multiline ? "textarea" : "input", {
        id,
        name: field.key,
        class: multiline ? "fo-textarea" : "fo-input",
        type: multiline ? null : "text",
        rows: multiline ? 4 : null,
        placeholder: field.placeholder,
        inputmode: INPUT_MODES[field.type],
        autocomplete: AUTOCOMPLETE[field.type] ?? "off",
        maxlength: hasLimit ? field.max_length : null,
    });

    const apply = () => {
        const raw = input.value;
        const position = input.selectionStart ?? raw.length;
        const clean = sanitize(field, raw);

        if (clean !== raw) {
            const caret = sanitize(field, raw.slice(0, position)).length;
            input.value = clean;
            input.setSelectionRange?.(caret, caret);
        }
        notify("input");
    };

    input.addEventListener("input", (event) => {
        if (!event.isComposing) apply();
    });
    input.addEventListener("compositionend", apply);
    input.addEventListener("blur", () => notify("blur"));

    return {
        node: input,
        focusEl: input,
        getValue: () => input.value.trim(),
        setValue: (value) => {
            input.value = sanitize(field, String(value ?? ""));
        },
    };
}

function buildSelect(field, id, notify) {
    const select = h(
        "select",
        { id, name: field.key },
        h("option", { value: "", text: field.placeholder || "Seleccione" }),
        ...field.options.map((option) => h("option", { value: option.value, text: option.label })),
    );

    select.addEventListener("change", () => notify("input"));
    select.addEventListener("blur", () => notify("blur"));

    return {
        node: h("div", { class: "fo-select" }, select),
        focusEl: select,
        getValue: () => select.value,
        setValue: (value) => {
            if (field.options.some((option) => option.value === value)) select.value = value;
        },
    };
}

function buildCheckboxes(field, id, notify) {
    const boxes = field.options.map((option, index) => {
        const input = h("input", { type: "checkbox", id: `${id}-${index}`, name: field.key, value: option.value });
        input.addEventListener("change", () => notify("input"));
        return { input, option };
    });

    return {
        mode: "group",
        node: h(
            "div",
            { class: "fo-options", role: "group", "aria-labelledby": `${id}-label` },
            ...boxes.map(({ input, option }, index) =>
                h("label", { class: "fo-check", for: `${id}-${index}` }, input, h("span", { text: option.label })),
            ),
        ),
        focusEl: boxes[0].input,
        getValue: () => boxes.filter(({ input }) => input.checked).map(({ input }) => input.value),
        setValue: (value) => {
            const wanted = Array.isArray(value) ? value : String(value).split(",");
            boxes.forEach(({ input }) => {
                input.checked = wanted.includes(input.value);
            });
        },
    };
}

function buildConsent(field, id, notify) {
    const input = h("input", { type: "checkbox", id, name: field.key });
    input.addEventListener("change", () => notify("input"));

    return {
        mode: "none",
        node: h("label", { class: "fo-consent", for: id }, input, h("span", { text: field.text || field.label })),
        focusEl: input,
        getValue: () => input.checked,
        setValue: () => {},
    };
}

function buildLocation(field, id, notify, required) {
    const department = h("select", { id: `${id}-department`, name: `${field.key}_department` });
    const municipality = h("select", { id: `${id}-municipality`, name: `${field.key}_municipality` });

    const fill = (select, items) =>
        select.replaceChildren(
            h("option", { value: "", text: "Seleccione" }),
            ...items.map((name) => h("option", { value: name, text: name })),
        );

    fill(department, Object.keys(geography));
    fill(municipality, []);
    municipality.disabled = true;

    department.addEventListener("change", () => {
        fill(municipality, geography[department.value] ?? []);
        municipality.disabled = !department.value;
        notify("input");
    });
    municipality.addEventListener("change", () => notify("input"));

    const column = (label, select) =>
        h(
            "div",
            {},
            h("label", { class: "fo-label", for: select.id, text: label + (required ? " *" : "") }),
            h("div", { class: "fo-select" }, select),
        );

    return {
        mode: "none",
        node: h(
            "div",
            { class: "fo-location" },
            column(field.department_label || "Departamento", department),
            column(field.municipality_label || "Municipio", municipality),
        ),
        focusEl: department,
        getValue: () => ({ department: department.value, municipality: municipality.value }),
        setValue: (value) => {
            if (!value || !geography[value.department]) return;
            department.value = value.department;
            fill(municipality, geography[value.department]);
            municipality.disabled = false;
            if (geography[value.department].includes(value.municipality)) municipality.value = value.municipality;
        },
    };
}

const BUILDERS = {
    select: buildSelect,
    checkbox_group: buildCheckboxes,
    consent: buildConsent,
    location: buildLocation,
};

function createNote(field) {
    return {
        root: h("div", { class: "fo-field fo-w-full" }, h("p", { class: "fo-note", text: field.text })),
    };
}

function createControl(field, uid, onEvent) {
    const id = `${uid}-${field.key}`;
    const required = field.required || field.type === "consent";
    const errorEl = h("p", { class: "fo-error", id: `${id}-error`, "aria-live": "polite" });

    let control;
    const notify = (kind) => onEvent(control, kind);
    control = (BUILDERS[field.type] ?? buildText)(field, id, notify, required);

    const star = required ? h("span", { class: "fo-req", text: "*" }) : null;
    const parts = [];

    if (!control.mode) parts.push(h("label", { class: "fo-label", for: id }, field.label, required && " ", star));
    else if (control.mode === "group") parts.push(h("span", { class: "fo-label", id: `${id}-label` }, field.label, required && " ", star));

    parts.push(control.node);
    if (field.help) parts.push(h("p", { class: "fo-help", text: field.help }));
    parts.push(errorEl);

    control.field = field;
    control.root = h("div", { class: `fo-field fo-w-${field.width === "half" ? "half" : "full"}` }, ...parts);
    control.setError = (message) => {
        errorEl.textContent = message || "";
        control.root.classList.toggle("fo-invalid", Boolean(message));
        control.focusEl.setAttribute("aria-invalid", message ? "true" : "false");
        if (message) control.focusEl.setAttribute("aria-describedby", errorEl.id);
        else control.focusEl.removeAttribute("aria-describedby");
    };

    return control;
}

/* ---------- Overlay ---------- */

function createShell(trigger) {
    const uid = `fo${++counter}`;
    const reduceMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;

    const closeButton = h(
        "button",
        { type: "button", class: "fo-close", "aria-label": "Cerrar" },
        h("i", { class: "ri-close-line", "aria-hidden": "true" }),
    );
    const content = h("div", { class: "fo-content" });
    const card = h("div", { class: "fo-card", role: "dialog", "aria-modal": "true", "aria-label": "Formulario" }, closeButton, content);
    const backdrop = h("div", { class: "fo-backdrop" }, card);

    const root = document.documentElement;
    const previous = { overflow: root.style.overflow, padding: document.body.style.paddingRight };
    const scrollbar = window.innerWidth - root.clientWidth;

    root.style.overflow = "hidden";
    if (scrollbar > 0) document.body.style.paddingRight = `${scrollbar}px`;
    document.body.append(backdrop);
    requestAnimationFrame(() => backdrop.classList.add("fo-open"));

    let dirty = false;
    let closed = false;
    let timer = null;

    const instance = {
        uid,
        card,
        content,
        markDirty: () => {
            dirty = true;
        },
        setTimer: (callback, ms) => {
            timer = setTimeout(callback, ms);
        },
        close: () => {
            if (closed) return;
            closed = true;
            clearTimeout(timer);
            document.removeEventListener("keydown", onKey, true);
            backdrop.remove();
            root.style.overflow = previous.overflow;
            document.body.style.paddingRight = previous.padding;
            if (active === instance) active = null;
            trigger?.focus?.({ preventScroll: true });
        },
        isClosed: () => closed,
        showThanks: (thanks) => {
            card.classList.add("fo-done");
            card.append(
                h(
                    "div",
                    { class: "fo-thanks", role: "status" },
                    h("p", { class: "fo-thanks-title", text: thanks.title || "¡Gracias!" }),
                    h("p", { class: "fo-thanks-sub", text: thanks.subtitle || "Hemos recibido tu registro." }),
                ),
            );
        },
        reduceMotion,
    };

    function onKey(event) {
        if (event.key === "Escape") {
            event.stopPropagation();
            instance.close();
            return;
        }
        if (event.key !== "Tab") return;

        const focusable = [...card.querySelectorAll('button, input, select, textarea, a[href]')].filter(
            (element) => !element.disabled && element.offsetParent !== null && element.tabIndex >= 0,
        );
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    document.addEventListener("keydown", onKey, true);
    closeButton.addEventListener("click", instance.close);
    backdrop.addEventListener("pointerdown", (event) => {
        if (event.target === backdrop && !dirty) instance.close();
    });

    return instance;
}

function showLoading(instance) {
    instance.content.replaceChildren(
        h("div", { class: "fo-state", role: "status" }, h("span", { class: "fo-spinner" }), h("p", { text: "Cargando formulario…" })),
    );
}

function showFailure(instance, retry, status) {
    const message =
        status === 404
            ? "Este formulario no está disponible en este momento."
            : "No pudimos cargar el formulario. Revisa tu conexión e inténtalo de nuevo.";
    const children = [h("p", { text: message })];

    if (status !== 404) {
        const button = h("button", { type: "button", class: "fo-submit", text: "Reintentar" });
        button.addEventListener("click", retry);
        children.push(button);
    }

    instance.content.replaceChildren(h("div", { class: "fo-state", role: "alert" }, ...children));
}

function renderForm(instance, slug, schema, prefill) {
    const items = schema.fields.map((field) =>
        field.type === "note"
            ? createNote(field)
            : createControl(field, instance.uid, (control, kind) => {
                  if (kind === "input") instance.markDirty();
                  const hasError = control.root.classList.contains("fo-invalid");

                  if (kind === "blur" && !isEmpty(control.field, control.getValue())) check(control);
                  else if (kind === "input" && hasError) check(control);
              }),
    );
    const controls = items.filter((item) => item.field);

    const byKey = new Map(controls.map((control) => [control.field.key, control]));

    function check(control) {
        const message = validate(control.field, control.getValue());
        control.setError(message);
        return message;
    }

    Object.entries(prefill).forEach(([key, value]) => byKey.get(key)?.setValue(value));

    const titleId = `${instance.uid}-title`;
    if (instance.inline) {
        instance.card.setAttribute("role", "region");
        instance.card.setAttribute("aria-label", schema.title);
    } else {
        instance.card.setAttribute("aria-labelledby", titleId);
        instance.card.removeAttribute("aria-label");
    }

    const alert = h("p", { class: "fo-alert", role: "alert", hidden: true });
    const submit = h("button", { type: "submit", class: "fo-submit", text: schema.submit_label || "Enviar" });
    const honeypot = h(
        "div",
        { class: "fo-hp", "aria-hidden": "true" },
        h("input", { type: "text", name: "website", tabindex: "-1", autocomplete: "off" }),
    );

    const form = h(
        "form",
        { class: "fo-form", novalidate: true },
        h("div", { class: "fo-grid" }, ...items.map((item) => item.root)),
        honeypot,
        alert,
        h("div", { class: "fo-actions" }, submit),
    );

    const header = [h("h2", { class: "fo-title", id: titleId, text: schema.title })];
    if (schema.description) header.push(h("p", { class: "fo-desc", text: schema.description }));

    instance.content.replaceChildren(...(instance.inline ? [] : header), form);

    const openedAt = Date.now();
    let sending = false;

    const setAlert = (message) => {
        alert.textContent = message || "";
        alert.hidden = !message;
    };

    function showThanks() {
        const thanks = schema.thanks || {};
        instance.showThanks(thanks);

        const seconds = Math.min(60, Math.max(1, Number(thanks.seconds) || 6));
        instance.setTimer(() => {
            if (thanks.redirect_url && isSafeRedirect(thanks.redirect_url)) {
                window.location.assign(thanks.redirect_url);
            } else {
                instance.close();
            }
        }, seconds * 1000);
    }

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (sending) return;

        setAlert("");
        const invalid = controls.filter((control) => check(control));
        if (invalid.length) {
            invalid[0].focusEl.focus();
            invalid[0].root.scrollIntoView({ block: "center", behavior: instance.reduceMotion ? "auto" : "smooth" });
            return;
        }

        sending = true;
        submit.disabled = true;
        submit.textContent = "Enviando…";

        const values = {};
        controls.forEach((control) => {
            values[control.field.key] = control.getValue();
        });

        try {
            const response = await fetch(`${formsUrl()}/${slug}/submissions`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    values,
                    version_id: schema.version_id,
                    website: honeypot.querySelector("input").value,
                    elapsed_ms: Date.now() - openedAt,
                    source_url: location.href,
                }),
            });

            if (response.ok) {
                showThanks();
                return;
            }

            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                let first = null;
                Object.entries(data.errors ?? {}).forEach(([key, messages]) => {
                    const control = byKey.get(key);
                    if (control) {
                        control.setError(messages[0]);
                        first ??= control;
                    } else {
                        setAlert(messages[0]);
                    }
                });
                first?.focusEl.focus();
            } else if (response.status === 429) {
                setAlert("Has hecho demasiados intentos. Espera unos minutos e inténtalo de nuevo.");
            } else if (response.status === 404) {
                setAlert("Este formulario ya no está disponible.");
            } else {
                setAlert("No pudimos enviar tu información. Inténtalo de nuevo en un momento.");
            }
        } catch {
            setAlert("No pudimos conectarnos. Revisa tu conexión e inténtalo de nuevo.");
        } finally {
            sending = false;
            submit.disabled = false;
            submit.textContent = schema.submit_label || "Enviar";
        }
    });

    if (!instance.inline) (controls[0]?.focusEl ?? form).focus({ preventScroll: true });
}

async function loadInto(instance, slug, prefill) {
    showLoading(instance);

    try {
        const schema = await getJson(`${formsUrl()}/${slug}`);
        if (schema.fields.some((field) => field.type === "location")) await loadGeography();
        if (instance.isClosed()) return;
        renderForm(instance, slug, schema, prefill);
    } catch (error) {
        if (!instance.isClosed()) {
            showFailure(instance, () => loadInto(instance, slug, prefill), error.status);
        }
    }
}

export async function openForm(slug, { prefill = {}, trigger = null } = {}) {
    if (active) return;

    const instance = createShell(trigger);
    active = instance;

    await loadInto(instance, slug, prefill);
}

export async function mountForm(container, slug, { prefill = {} } = {}) {
    if (container.dataset.foMounted) return;
    container.dataset.foMounted = "true";

    const content = h("div", { class: "inf-content" });
    container.replaceChildren(content);

    let timer = null;

    const instance = {
        uid: `fo${++counter}`,
        inline: true,
        card: container,
        content,
        reduceMotion: matchMedia("(prefers-reduced-motion: reduce)").matches,
        markDirty: () => {},
        setTimer: (callback, ms) => {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        },
        isClosed: () => !container.isConnected,
        showThanks: (thanks) => {
            container.style.minHeight = `${container.offsetHeight}px`;
            content.replaceChildren(
                h(
                    "div",
                    { class: "inf-thanks", role: "status" },
                    h("p", { class: "inf-thanks-title", text: thanks.title || "¡Gracias!" }),
                    h("p", { class: "inf-thanks-sub", text: thanks.subtitle || "Hemos recibido tu registro." }),
                ),
            );
        },
        close: () => {
            container.style.minHeight = "";
            return loadInto(instance, slug, prefill);
        },
    };

    await loadInto(instance, slug, prefill);
}