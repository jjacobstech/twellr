import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';


/** @type {import('tailwindcss').Config} */
export default
    {
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
                    'navy-blue': '#001f54',
                    'golden': '#fbaa0d',
                },
                keyframes: {
                    'swing': {
                        '0%,100%': { transform: 'rotate(15deg)' },
                        '50%': { transform: 'rotate(-15deg)' },
                    }
                },
                animation: {
                    'swing': 'swing 1s infinite'
                }

            },

        },

        plugins: [forms],
        darkMode: 'class'
    };
