/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    safelist: [
        { pattern: /bg-\[#(003B71|E97300|dce8f5|002a52|c96200)\]/ },
        { pattern: /text-\[#(003B71|E97300|dce8f5|002a52|ffffff)\]/ },
        { pattern: /border-\[#(003B71|E97300|dce8f5)\]/ },
        { pattern: /hover:bg-\[#(003B71|E97300|dce8f5|002a52|c96200)\]/ },
        { pattern: /hover:text-\[#(003B71|E97300|ffffff)\]/ },
        'bg-white/10', 'bg-white/20', 'bg-white/30', 'bg-black/50',
        'border-white/20', 'border-white/30',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
