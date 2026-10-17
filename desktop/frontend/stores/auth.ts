import { defineStore } from 'pinia'

export interface User {
  id: number
  name: string
  email: string
  phone: string
  nip_nis: string
  role: 'SISWA' | 'SEKRETARIS' | 'GURU_PENGAJAR' | 'GURU_PEMBIMBING'
  is_active: boolean
  class_id?: number
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

  // Initialize from localStorage in client
  if (import.meta.client) {
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')
    if (savedToken) token.value = savedToken
    if (savedUser) {
      try {
        user.value = JSON.parse(savedUser)
      } catch (e) {
        console.error(e)
      }
    }
  }

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role = computed(() => user.value?.role || 'SISWA')

  function setAuth(newUser: User, newToken: string) {
    user.value = newUser
    token.value = newToken
    if (import.meta.client) {
      localStorage.setItem('token', newToken)
      localStorage.setItem('user', JSON.stringify(newUser))
    }
  }

  async function login(email: string, password: string) {
    const res = await $fetch<{ message: string; user: User; token: string }>(`${apiBase}/auth/login`, {
      method: 'POST',
      body: { email, password },
    })
    setAuth(res.user, res.token)
    return res
  }

  async function quickLogin(selectedRole: string) {
    const res = await $fetch<{ message: string; user: User; token: string }>(`${apiBase}/auth/quick-login`, {
      method: 'POST',
      body: { role: selectedRole },
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
    }
    navigateTo('/login')
  }

  return {
    user,
    token,
    role,
    isAuthenticated,
    apiBase,
    setAuth,
    login,
    quickLogin,
    logout,
  }
})
