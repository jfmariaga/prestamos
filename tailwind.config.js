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
                    50: '#eef2f8',
                    100: '#dbe4f0',
                    200: '#b4c7e0',
                    300: '#8ca9cf',
                    400: '#5c80ae',
                    500: '#395f8c',
                    600: '#254872',
                    700: '#1b355a',
                    800: '#14263f',
                    900: '#0d1a2b',
                },
                accent: {
                    50: '#fef9ec',
                    100: '#fcefc6',
                    200: '#f8dd8a',
                    300: '#f3c74d',
                    400: '#efb524',
                    500: '#d99812',
                    600: '#b3760d',
                    700: '#8c5a0e',
                    800: '#724a12',
                    900: '#5f3e14',
                },
            },
        },
    },

    plugins: [forms],
};
