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
          DEFAULT: '#C62828',
          hover: '#B71C1C',
          light: '#FEE2E2',
          subtle: '#FEF2F2'
        },
        'brand-red': {
          DEFAULT: '#C62828',
          dark: '#B71C1C',
          light: '#FEE2E2',
          50: '#FEF2F2'
        },
        presence: {
          hadir: '#16A34A',
          'hadir-bg': '#DCFCE7',
          sakit: '#EA580C',
          'sakit-bg': '#FFEDD5',
          izin: '#2563EB',
          'izin-bg': '#DBEAFE',
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
