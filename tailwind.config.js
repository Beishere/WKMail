import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eef4ff',
                    100: '#d9e6ff',
                    200: '#bcd3ff',
                    300: '#8eb6ff',
                    400: '#598dff',
                    500: '#3366ff',
                    600: '#1d47f5',
                    700: '#1634e1',
                    800: '#192cb6',
                    900: '#1a2b8f',
                    950: '#151c57',
                },
            },
        },
    },
    plugins: [forms],
};
