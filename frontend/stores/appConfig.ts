import { defineStore } from 'pinia'

export const useAppConfigStore = defineStore('appConfig', {
  state: () => ({
    school_name: 'Jurnal Kelas Digital',
    school_npsn: '',
    school_address: '',
    school_logo_url: '/images/logo-smkn71.png',
    headmaster_name: '',
    active_term: '2026/2027 • Semester Ganjil',
    majors: ['RPL'] as string[],
    enable_demo_login: true,
    loaded: false,
    loading: false,
  }),

  actions: {
    async fetchConfig(force = false) {
      if (this.loaded && !force) return
      if (this.loading) return

      this.loading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase || '/api'
        const res = await $fetch<any>(`${apiBase}/app/info`)
        if (res) {
          if (res.school_name) this.school_name = res.school_name
          if (res.school_npsn) this.school_npsn = res.school_npsn
          if (res.school_address) this.school_address = res.school_address
          if (res.school_logo_url) this.school_logo_url = res.school_logo_url
          if (res.headmaster_name) this.headmaster_name = res.headmaster_name
          if (res.active_term) this.active_term = res.active_term
          if (Array.isArray(res.majors) && res.majors.length) {
            this.majors = res.majors
          }
          if (typeof res.enable_demo_login === 'boolean') {
            this.enable_demo_login = res.enable_demo_login
          }
          this.loaded = true
        }
      } catch (e) {
        // Fallback default
        this.loaded = true
      } finally {
        this.loading = false
      }
    }
  }
})
