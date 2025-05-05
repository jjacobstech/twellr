import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
// import daisyui from 'daisyui';


/** @type {import('tailwindcss').Config} */
export default
    {
        content: [
            './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
            './storage/framework/views/*.php',
            './resources/views/**/*.blade.php',
            "./vendor/robsontenorio/mary/src/View/Components/**/*.php"
        ],

        theme: {
            screens: {
                'sm': '640px',
                'md': '768px',
                'lg': '1024px',
                'xl': '1280px',
                '2xl': '1536px',
            },
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

        plugins: [
            forms,
            require('daisyui'),
            require('tailwind-scrollbar')
        ],
darkMode:"class"
    };
