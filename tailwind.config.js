import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f0f5fe',
                    100: '#e4ecfd',
                    200: '#c2d7fb',
                    300: '#8bb5f7',
                    400: '#4e8cf0',
                    500: '#276ae6', // color base azul rey iluminado
                    600: '#184ec9',
                    700: '#143da3',
                    800: '#153585',
                    900: '#152f6b',
                    950: '#0f1d43',
                },
                accent: {
                    DEFAULT: '#A855F7', // un púrpura tipo neon
                    neon: '#d946ef',
                },
                surface: {
                    50: '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    DEFAULT: '#09090b', // dark surface
                    light: '#18181b', // elevated dark surface
                    lighter: '#27272a',
                }
            },
        },
    },

    plugins: [forms],
};
