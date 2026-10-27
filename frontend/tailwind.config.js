/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './app.vue',
    './plugins/**/*.{js,ts}'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0f47ad',
          hover: '#0c398a',
          dark: '#082457',
          light: '#cfdaef',
          subtle: '#eff4fc'
        },
        'brand-red': {
          DEFAULT: '#0f47ad',
          dark: '#0c398a',
          light: '#cfdaef',
          50: '#eff4fc'
        },
        'brand-blue': {
          DEFAULT: '#0f47ad',
          dark: '#0c398a',
          light: '#cfdaef',
          50: '#eff4fc'
        },
        presence: {
          hadir: '#16A34A',
          'hadir-bg': '#DCFCE7',
          sakit: '#EA580C',
          'sakit-bg': '#FFEDD5',
          izin: '#0f47ad',
          'izin-bg': '#cfdaef',
          alpha: '#DC2626',
          'alpha-bg': '#FEE2E2'
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif']
      }
    }
  },
  plugins: []
}
