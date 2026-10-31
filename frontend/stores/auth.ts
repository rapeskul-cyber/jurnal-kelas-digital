import { defineStore } from 'pinia'

export interface User {
  id: number
  name: string
  email: string
  phone: string
  nip_nis: string
  role: 'SISWA' | 'SEKRETARIS' | 'GURU_PENGAJAR' | 'GURU_PEMBIMBING' | 'ORANG_TUA' | 'SUPERADMIN'
  specialization?: string
  is_active: boolean
  class_id?: number
  avatar?: string | null
  avatar_url?: string | null
  school_class?: {
    id: number
    name: string
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || '/api'

  function clearDashboardCache() {
    if (import.meta.client) {
      const roles = ['SISWA', 'SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'ORANG_TUA', 'SUPERADMIN', 'default']
      roles.forEach((r) => localStorage.removeItem(`cache_dashboard_${r}`))
    }
  }

  function ensureAuth() {
    if (import.meta.client) {
      if (!token.value) {
        const savedToken = localStorage.getItem('token')
        if (savedToken) token.value = savedToken
      }
      if (!user.value) {
        const savedUser = localStorage.getItem('user')
        if (savedUser) {
          try {
            user.value = JSON.parse(savedUser)
          } catch (e) {
            console.error(e)
          }
        }
      }
    }
  }

  // Initialize from localStorage in client
  ensureAuth()

  const isAuthenticated = computed(() => {
    ensureAuth()
    return !!token.value && !!user.value
  })
  const role = computed(() => user.value?.role || 'SISWA')

  function setAuth(newUser: User, newToken: string) {
    clearDashboardCache()
    user.value = newUser
    token.value = newToken
    if (import.meta.client) {
      localStorage.setItem('token', newToken)
      localStorage.setItem('user', JSON.stringify(newUser))
    }
  }

  async function login(email: string, password: string, turnstileToken?: string) {
    const body: Record<string, any> = { email, password }
    if (turnstileToken) {
      body.turnstile_token = turnstileToken
    }
    const res = await $fetch<{ message: string; user: User; token: string }>(`${apiBase}/auth/login`, {
      method: 'POST',
      body,
    })
    setAuth(res.user, res.token)
    return res
  }

  async function logout() {
    if (token.value) {
      try {
        await $fetch(`${apiBase}/auth/logout`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${token.value}` },
        })
      } catch (e) {
        // ignore network error on logout
      }
    }
    user.value = null
    token.value = null
    if (import.meta.client) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      clearDashboardCache()
    }
    navigateTo('/login')
  }

  return {
    user,
    token,
    role,
    isAuthenticated,
    apiBase,
    ensureAuth,
    setAuth,
    login,
    logout,
  }
})
