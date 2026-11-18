/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                sky: {
                    DEFAULT: '#1E88E5',
                    light: '#2196F3',
                },
                lime: {
                    DEFAULT: '#C6F432',
                },
                ink: {
                    DEFAULT: '#0A0A0A',
                },
                muted: '#6B7280',
                glass: 'rgba(255,255,255,0.25)',
                nodeBg: '#FFFFFF',
                nodeBorder: '#E2E8F0',
                purple: {
                    soft: '#E9D5FF',
                    DEFAULT: '#A78BFA',
                    deep: '#7C5CFC',
                },
                pastel: {
                    green: '#E8F5E9',
                    dark: '#F1F8E9',
                },
            },
            fontFamily: {
                heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                sans: ['"Inter"', 'sans-serif'],
            },
            boxShadow: {
                card: '0 10px 30px rgba(0, 0, 0, 0.08)',
                node: '0 4px 12px rgba(0, 0, 0, 0.05)',
                'node-hover': '0 12px 28px rgba(59, 130, 246, 0.12)',
            },
        },
    },
    plugins: [],
};
