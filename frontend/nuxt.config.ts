// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,
  devServer: {
    port: 3001
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },
  srcDir: '.',
  css: ['~/assets/css/main.css'],
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],
  vite: {
    optimizeDeps: {
      include: ['lucide-vue-next', 'chart.js', 'vue-chartjs']
    }
  },
  app: {
    head: {
      title: 'Jurnal Kelas Digital - Edisi Digital',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover' },
        { name: 'theme-color', content: '#0f47ad' },
        { name: 'mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'default' },
        { name: 'description', content: 'Web Jurnal Kelas Digital, Presensi & Jurnal Pembelajaran Siswa dan Guru' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' }
      ],
      script: [
        {
          src: 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
          async: true,
          defer: true
        }
      ]
    }
  },
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api',
      turnstileSiteKey: process.env.NUXT_PUBLIC_TURNSTILE_SITE_KEY || process.env.TURNSTILE_SITE_KEY || ''
    }
  },
  nitro: {
    routeRules: {
      '/**': {
        headers: {
          'X-Frame-Options': 'SAMEORIGIN',
          'X-Content-Type-Options': 'nosniff',
          'Referrer-Policy': 'strict-origin-when-cross-origin'
        }
      },
      '/api/**': { proxy: (process.env.BACKEND_PROXY_URL || 'http://127.0.0.1:8000') + '/api/**' },
      '/storage/**': { proxy: (process.env.BACKEND_PROXY_URL || 'http://127.0.0.1:8000') + '/storage/**' }
    }
  }
})
