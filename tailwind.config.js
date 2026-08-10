import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#1e293b', // soft black (slate-800)
                    hover: '#0f172a',   // deeper black (slate-900)
                    accent: '#64748b',  // muted slate
                    soft: '#f8fafc',    // soft white
                    ink: '#0f172a',
                },
            },
            boxShadow: {
                card: '0 1px 3px rgb(15 23 42 / 0.06), 0 1px 2px rgb(15 23 42 / 0.04)',
            },
        },
    },

    plugins: [forms],
};
