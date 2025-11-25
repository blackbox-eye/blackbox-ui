/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.php',
    './products.php',
    './pricing.php',
    './contact.php',
    './faq.php',
    './demo.php',
    './free-scan.php',
    './includes/**/*.php',
    './assets/js/**/*.js',
    './lang/**/*.json'
  ],
  darkMode: 'media',
  theme: {
    container: {
      center: true,
      padding: {
        DEFAULT: '1rem',
        sm: '1.25rem',
        lg: '2rem'
      }
    },
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Chakra Petch', 'Inter', 'ui-sans-serif', 'system-ui']
      },
      screens: {
        'xs': '480px'
      },
      colors: {
        brand: {
          gold: '#FFC700',
          goldDark: '#d4af37'
        },
        amber: {
          400: '#fbbf24',
          500: '#f59e0b'
        },
        gray: {
          950: '#0d1115',
          900: '#111827',
          800: '#1f2937',
          700: '#374151',
          600: '#4b5563',
          500: '#6b7280',
          400: '#9ca3af',
          300: '#d1d5db'
        }
      },
      boxShadow: {
        glass: '0 4px 30px rgba(0,0,0,0.3)'
      },
      backdropBlur: {
        xs: '2px'
      }
    }
  },
  plugins: [],
};

