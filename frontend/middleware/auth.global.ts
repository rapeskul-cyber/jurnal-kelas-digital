import { useAuthStore } from '~/stores/auth'

const ROLE_ALLOWED_ROUTES: Record<string, string[]> = {
  SISWA: [
    '/dashboard',
    '/profile',
    '/jurnal-pelajaran',
    '/riwayat-kehadiran',
    '/surat-izin',
    '/notifikasi',
  ],
  SEKRETARIS: [
    '/dashboard',
    '/profile',
    '/presensi',
    '/jurnal-kelas',
    '/surat-izin',
    '/laporan-jurnal',
    '/jurnal-pelajaran',
    '/notifikasi',
  ],
  GURU_PENGAJAR: [
    '/dashboard',
    '/profile',
    '/presensi',
    '/jurnal-kelas',
    '/jurnal-materi',
    '/laporan-jurnal',
    '/rekap-semester',
    '/notifikasi',
  ],
  GURU_PEMBIMBING: [
    '/dashboard',
    '/profile',
    '/presensi',
    '/persetujuan-izin',
    '/laporan-izin',
    '/manajemen-user',
    '/laporan-jurnal',
    '/rekap-semester',
    '/notifikasi',
  ],
}

export default defineNuxtRouteMiddleware((to, from) => {
  // Public pages
  if (to.path === '/login' || to.path.startsWith('/forgot-password')) {
    return
  }

  // Client-side execution
  if (process.client) {
    const authStore = useAuthStore()

    // 1. Check Authentication
    if (!authStore.isAuthenticated) {
      // Check localStorage token directly before rejecting
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      if (token && user) {
        authStore.token = token
        authStore.user = JSON.parse(user)
      } else {
        return navigateTo('/login')
      }
    }

    // 2. Redirect root path to dashboard
    if (to.path === '/') {
      return navigateTo('/dashboard')
    }

    // 3. Check Role-Based Access Control (RBAC)
    const userRole = authStore.user?.role
    if (userRole && ROLE_ALLOWED_ROUTES[userRole]) {
      const allowed = ROLE_ALLOWED_ROUTES[userRole]
      const isAllowed = allowed.some((prefix) => to.path === prefix || to.path.startsWith(prefix + '/'))
      if (!isAllowed) {
        return navigateTo('/dashboard')
      }
    }
  }
})
