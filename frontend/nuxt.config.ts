// Nuxt 3 configuration
export default {
  devtools: { enabled: false },
  modules: [
    '@nuxtjs/tailwindcss',
  ],
  css: [
    '~/assets/styles/main.css'
  ],
  compatibilityDate: '2024-04-03',
  build: {
    transpile: ['vue']
  },
  app: {
    head: {
      title: 'Jurnal Kelas Digital',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' }
      ]
    }
  }
}
