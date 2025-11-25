/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './**/*.html',
    './assets/js/**/*.js',
    './lang/**/*.json'
  ],
  darkMode: 'media',
  theme: {
    extend: {
      colors: {
        amber: {
          400: '#fbbf24',
          500: '#f59e0b'
        },
        gray: {
          900: '#111827',
          800: '#1f2937',
          700: '#374151',
          600: '#4b5563',
          400: '#9ca3af',
          300: '#d1d5db'
        }
      }
    },
  },
  plugins: [],
};

