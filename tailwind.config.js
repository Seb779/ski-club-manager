/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                ski: {
                    50:  '#eef2ff',
                    100: '#e0e7ff',
                    500: '#3b5bdb',
                    600: '#2f4ac4',
                    700: '#2540a8',
                    800: '#1c3190',
                    900: '#152478',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
