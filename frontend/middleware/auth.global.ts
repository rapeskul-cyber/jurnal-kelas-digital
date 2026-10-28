import { useAuthStore } from '~/stores/auth'

const ROLE_ALLOWED_ROUTES: Record<string, string[]> = {
  SISWA: [
    '/dashboard',
    '/profile',
    '/jurnal-pelajaran',
    '/jurnal-kelas',
    '/surat-izin',
    '/riwayat-kehadiran',
    '/notifikasi',
  ],
  SEKRETARIS: [
    '/dashboard',
    '/profile',
    '/jurnal-kelas',
    '/surat-izin',
    '/laporan-jurnal',
    '/jurnal-pelajaran',
    '/riwayat-kehadiran',
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
    '/jurnal-kelas',
    '/persetujuan-izin',
    '/laporan-izin',
    '/manajemen-murid',
    '/manajemen-user',
    '/laporan-jurnal',
    '/rekap-semester',
    '/notifikasi',
    '/broadcast',
  ],
  ORANG_TUA: [
    '/dashboard',
    '/profile',
    '/jurnal-pelajaran',
    '/riwayat-kehadiran',
    '/surat-izin',
    '/notifikasi',
  ],
  SUPERADMIN: [
    '/dashboard',
    '/profile',
    '/master-data',
    '/manajemen-guru',
    '/manajemen-user',
    '/presensi-override',
    '/wa-gateway',
    '/broadcast',
    '/pengaturan-sistem',
    '/presensi',
    '/jurnal-kelas',
    '/jurnal-materi',
    '/persetujuan-izin',
    '/laporan-izin',
    '/notifikasi',
  ],
}

export default defineNuxtRouteMiddleware((to, from) => {
  // Public pages
  if (to.path === '/' || to.path === '/login' || to.path.startsWith('/forgot-password')) {
    return
  }

  // Client-side execution
  if (process.client) {
    const authStore = useAuthStore()

    // 1. Check Authentication
    if (!authStore.isAuthenticated) {
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
