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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                gaming: ['Rajdhani', 'sans-serif'],
            },
            colors: {
                primary: {
                    DEFAULT: '#FF9500',
                    50: '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7AA',
                    300: '#FDBA74',
                    400: '#FB923C',
                    500: '#FF9500',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
                    950: '#431407',
                },
                dark: {
                    bg: '#0b0e14',
                    surface: '#161b22',
                    border: '#30363d',
                },
                // Palette émeraude pour le light mode player
                emerald: {
                    DEFAULT: '#059669',
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
                // Palette parchemin/ivoire pour le light mode player
                parchment: {
                    DEFAULT: '#faf6ee',
                    50: '#fffdf7',
                    100: '#faf6ee',
                    200: '#f5edd8',
                    300: '#ede0c0',
                    400: '#e0cfa0',
                    500: '#c8a96e',
                    600: '#a8843a',
                    700: '#8b6914',
                    800: '#6b4c1e',
                    900: '#3d2a0a',
                    950: '#1a1208',
                },
            },
        },
    },

    plugins: [forms],
};
