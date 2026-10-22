const NAME_DISALLOWED = /[^\p{L}\p{M} '’-]/gu;
const NAME_VALID = /^\p{L}[\p{L}\p{M} '’-]*$/u;
const EMAIL_VALID = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

const allowsNegative = (field) => field.min == null || field.min < 0;
const digitsOnly = (value, max) => value.replace(/\D/g, "").slice(0, max);

export function sanitize(field, value) {
    switch (field.type) {
        case "proper_name":
            return value
                .replace(NAME_DISALLOWED, "")
                .replace(/^[^\p{L}]+/u, "")
                .replace(/ {2,}/g, " ")
                .slice(0, field.max_length ?? 150);
        case "dui":
            return digitsOnly(value, 9);
        case "nit":
            return digitsOnly(value, 14);
        case "phone_sv":
            return digitsOnly(value, 8);
        case "phone_intl":
            return (value.trimStart().startsWith("+") ? "+" : "") + digitsOnly(value, 15);
        case "email":
            return value.replace(/\s/g, "").slice(0, 190);
        case "integer": {
            const negative = allowsNegative(field) && value.trimStart().startsWith("-");
            return (negative ? "-" : "") + digitsOnly(value, 15);
        }
        case "decimal": {
            const decimals = field.decimals ?? 2;
            const text = value.replace(/,/g, ".");
            const negative = allowsNegative(field) && text.trimStart().startsWith("-");
            const [integer = "", ...rest] = text.replace(/[^\d.]/g, "").split(".");
            const fraction = rest.join("").slice(0, decimals);
            return (negative ? "-" : "") + integer.slice(0, 15) + (rest.length ? "." + fraction : "");
        }
        case "text":
            return value.replace(/[\r\n\t]/g, " ").slice(0, field.max_length ?? 255);
        case "textarea":
            return value.slice(0, field.max_length ?? 1000);
        default:
            return value;
    }
}

export function initialValue(field) {
    switch (field.type) {
        case "checkbox_group":
            return [];
        case "consent":
            return false;
        case "location":
            return { department: "", municipality: "" };
        default:
            return "";
    }
}

export function isEmpty(field, value) {
    switch (field.type) {
        case "checkbox_group":
            return !value.length;
        case "consent":
            return !value;
        case "location":
            return !value.department && !value.municipality;
        default:
            return String(value).trim() === "";
    }
}

function duiCheckDigitIsValid(dui) {
    let sum = 0;
    for (let i = 0; i < 8; i++) sum += Number(dui[i]) * (9 - i);
    return (10 - (sum % 10)) % 10 === Number(dui[8]);
}

function rangeError(field, number) {
    if (field.min != null && number < field.min) return `El valor mínimo es ${field.min}.`;
    if (field.max != null && number > field.max) return `El valor máximo es ${field.max}.`;
    return "";
}

export function validate(field, value) {
    const required = field.required || field.type === "consent";

    if (isEmpty(field, value)) {
        if (field.type === "consent") return "Debes aceptar para continuar.";
        if (field.type === "checkbox_group") return required ? "Elige al menos una opción." : "";
        if (field.type === "location") return required ? "Elige departamento y municipio." : "";
        return required ? "Este campo es obligatorio." : "";
    }

    const text = typeof value === "string" ? value.trim() : value;

    switch (field.type) {
        case "proper_name":
            return NAME_VALID.test(text) ? "" : "Usa solo letras, espacios, apóstrofe y guion.";
        case "dui":
            if (!/^\d{9}$/.test(text)) return "El DUI debe tener 9 dígitos, sin guion.";
            return field.verify_check_digit && !duiCheckDigitIsValid(text) ? "El DUI no es válido." : "";
        case "nit":
            return /^\d{14}$/.test(text) ? "" : "El NIT debe tener 14 dígitos, sin guion.";
        case "phone_sv":
            return /^\d{8}$/.test(text) ? "" : "El teléfono debe tener 8 dígitos.";
        case "phone_intl":
            return /^\+\d{8,15}$/.test(text) ? "" : "Escribe el teléfono con código de país, por ejemplo +50370001234.";
        case "email":
            return EMAIL_VALID.test(text) ? "" : "Escribe un correo válido.";
        case "integer":
            return /^-?\d+$/.test(text) ? rangeError(field, Number(text)) : "Escribe un número entero.";
        case "decimal": {
            const pattern = new RegExp(`^-?\\d+(\\.\\d{1,${field.decimals ?? 2}})?$`);
            return pattern.test(text)
                ? rangeError(field, Number(text))
                : `Escribe un número con hasta ${field.decimals ?? 2} decimales.`;
        }
        case "select":
            return field.options.some((option) => option.value === text) ? "" : "Elige una opción válida.";
        case "location":
            if (!text.department) return "Elige un departamento.";
            return text.municipality ? "" : "Elige un municipio.";
        default:
            return "";
    }
}