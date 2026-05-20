/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    safelist: [
        // Colores del banco
        { pattern: /bg-\[#(003B71|E97300|dce8f5|002a52|c96200)\]/ },
        { pattern: /text-\[#(003B71|E97300|dce8f5|002a52|ffffff)\]/ },
        { pattern: /border-\[#(003B71|E97300|dce8f5)\]/ },
        { pattern: /hover:bg-\[#(003B71|E97300|dce8f5|002a52|c96200)\]/ },
        { pattern: /hover:text-\[#(003B71|E97300|ffffff)\]/ },
        // Opacidades
        { pattern: /bg-(white|black)\/(10|20|30|50)/ },
        { pattern: /border-white\/(20|30)/ },
        // Spacing
        { pattern: /p-(4|5|6|8|12)/ },
        { pattern: /px-(4|5|6|8)/ },
        { pattern: /py-(2|3|6|8|12|16|20)/ },
        { pattern: /gap-(1|2|3|4|5|6|8|12)/ },
        { pattern: /mb-(3|4|6|8|12)/ },
        { pattern: /mt-(4|auto)/ },
        // Typography
        { pattern: /text-(xs|sm|base|lg|xl|2xl|4xl|5xl)/ },
        { pattern: /font-(medium|semibold|bold)/ },
        { pattern: /leading-(relaxed|tight|snug)/ },
        // Sizing
        { pattern: /w-(11|12|14|16|20|full)/ },
        { pattern: /h-(11|12|14|16|20)/ },
        { pattern: /max-w-(xs|sm|md|lg|xl|2xl|6xl|7xl)/ },
        // Border radius
        { pattern: /rounded-(lg|xl|2xl|full)/ },
        // Flex/Grid
        { pattern: /flex-(col|row|wrap|1)/ },
        { pattern: /grid-cols-(1|2|3|4)/ },
        { pattern: /order-(1|2)/ },
        // Colors
        { pattern: /text-(white|gray)-(200|300|600|700|900)/ },
        { pattern: /bg-(white|gray)-(50|100|200)/ },
        { pattern: /border-(white|gray)-(200|300)/ },
        // Misc
        'flex', 'grid', 'hidden', 'block', 'inline-block', 'relative',
        'absolute', 'overflow-hidden', 'overflow-y-auto', 'shrink-0',
        'items-center', 'items-start', 'items-stretch',
        'justify-center', 'justify-between',
        'text-center', 'text-left', 'text-white',
        'mx-auto', 'mt-auto', 'border', 'border-2',
        'transition-all', 'duration-200', 'duration-300',
        'opacity-0', 'cursor-pointer', 'pointer-events-none',
        'shadow-lg', 'shadow-xl', 'shadow-2xl',
        'object-cover', 'object-contain',
        'inset-0', 'z-10', 'z-20',
        '-translate-y-1/2', '-translate-x-1/2',
        'animate-spin', 'space-y-3', 'space-y-4',
        'hover:opacity-90', 'hover:shadow-xl', 'hover:underline',
        'hover:scale-110', 'hover:bg-gray-100',
        'sm:flex-row', 'lg:flex-row', 'lg:w-1/3', 'lg:w-2/3',
        'bg-white', 'bg-transparent', 'bg-black',
        'text-gray-200', 'text-gray-300', 'text-gray-600',
        'border-gray-200', 'border-gray-300',
        'rounded-2xl', 'rounded-xl', 'rounded-lg', 'rounded-full',
        'max-w-6xl', 'max-w-2xl', 'max-w-7xl',
        'w-full', 'h-full', 'min-h-screen',
        'py-12', 'px-6', 'p-6', 'p-8',
        'mb-12', 'mb-8', 'mb-4', 'mb-3',
        'gap-4', 'gap-6', 'gap-12',
        'text-4xl', 'text-base', 'font-bold', 'font-semibold',
        'leading-relaxed',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
