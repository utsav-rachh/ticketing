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
                sans: ['Inter', 'DM Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#E6F0F9',
                    100: '#CCE0F3',
                    200: '#99C2E7',
                    300: '#66A3DB',
                    400: '#007BFF',
                    500: '#0056B3',
                    600: '#004A99',
                    700: '#003D80',
                    800: '#002E52',
                    900: '#001A33',
                },
                gold: {
                    50: '#E6F9F9',
                    100: '#B3EDED',
                    200: '#80E0E0',
                    300: '#4DD4D4',
                    400: '#00B3B3',
                    500: '#009999',
                    600: '#008080',
                    700: '#006666',
                    800: '#004D4D',
                    900: '#003333',
                },
            },
        },
    },

    plugins: [forms],
};
