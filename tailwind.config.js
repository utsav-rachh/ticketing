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
                    50: '#E8F4FA',
                    100: '#C5E3F3',
                    200: '#8DC7E7',
                    300: '#55ABDB',
                    400: '#1A9AD4',
                    500: '#107AB0',
                    600: '#0C5F8A',
                    700: '#094A6C',
                    800: '#06344D',
                    900: '#031F2E',
                },
                gold: {
                    50: '#FDF8EB',
                    100: '#F9ECCC',
                    200: '#F0D78F',
                    300: '#E8C35E',
                    400: '#D4A843',
                    500: '#B8922F',
                    600: '#9A7A25',
                    700: '#7C621D',
                    800: '#5E4A16',
                    900: '#40320F',
                },
            },
        },
    },

    plugins: [forms],
};
