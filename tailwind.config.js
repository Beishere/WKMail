import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/**/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eef0ff',
                    100: '#dfe3ff',
                    200: '#c6ccff',
                    300: '#a3abfe',
                    400: '#7d83fa',
                    500: '#5e63c5',
                    600: '#2d338c',
                    700: '#252a75',
                    800: '#1e2260',
                    900: '#171a4a',
                    950: '#0f1133',
                },
                ukwk: {
                    50: '#fff6ed',
                    100: '#ffebd4',
                    200: '#ffd3a8',
                    300: '#ffb370',
                    400: '#ff854d',
                    500: '#ed6623',
                    600: '#d95316',
                    700: '#b43e12',
                    800: '#903216',
                    900: '#3e2214',
                },
            },
        },
    },
    plugins: [forms],
};
