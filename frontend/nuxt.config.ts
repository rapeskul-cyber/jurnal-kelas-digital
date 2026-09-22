// Nuxt 3 configuration
export default defineNuxtConfig({
  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],

  css: [
    '~/assets/styles/main.css'
  ],

  app: {
    head: {
      title: 'Jurnal Kelas Digital',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Platform jurnal pembelajaran digital untuk sekolah' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_API_BASE || 'http://localhost:8000/api'
    }
  },

  tailwindcss: {
    cssPath: '~/assets/styles/main.css',
    configPath: 'tailwind.config.js',
    viewer: false
  },

  typescript: {
    strict: true
  },

  nitro: {
    compressPublicAssets: true
  }
})