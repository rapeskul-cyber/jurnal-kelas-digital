<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col justify-center items-center p-4 py-8">
    <!-- Auth Card -->
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm text-center my-auto">
      <!-- Logo Icon -->
      <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-sm">
        <Book class="w-6 h-6" />
      </div>

      <!-- Title & Subtitle -->
      <h1 class="text-xl font-bold text-gray-900 tracking-tight">Masuk Jurnal Kelas</h1>
      <p class="text-xs text-gray-500 mt-1 mb-8">Sistem Presensi & Jurnal Digital</p>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left">
        {{ errorMessage }}
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-5 text-left">
        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Masukkan Akun Email
          </label>
          <input
            v-model="email"
            type="email"
            required
            placeholder="nama@sekolah.sch.id"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>

        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Kata Sandi
          </label>
          <div class="relative">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••••••"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all pr-11"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1"
            >
              <EyeOff v-if="showPassword" class="w-4 h-4" />
              <Eye v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover active:scale-[0.99] text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 mt-2 disabled:opacity-70 cursor-pointer"
        >
          <span v-if="isLoading">Memproses...</span>
          <span v-else>Masuk Ke Dashboard</span>
        </button>

        <!-- Forgot Password Link -->
        <div class="text-center pt-2">
          <NuxtLink to="/forgot-password" class="text-xs text-primary hover:underline font-medium">
            Lupa kata sandi?
          </NuxtLink>
        </div>
      </form>

      <!-- Quick Login Section (Dev & Demo) -->
      <div class="mt-8 pt-6 border-t border-gray-100">
        <p class="text-[10px] uppercase font-bold text-gray-400 mb-3 tracking-wider">Akses Cepat (Demo Role)</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <button
            type="button"
            @click="handleQuickLogin('SEKRETARIS')"
            class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-primary hover:border-primary/40 bg-gray-50/50 hover:bg-red-50/30 transition-colors text-[11px] font-medium"
          >
            Sekretaris
          </button>
          <button
            type="button"
            @click="handleQuickLogin('SISWA')"
            class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-primary hover:border-primary/40 bg-gray-50/50 hover:bg-red-50/30 transition-colors text-[11px] font-medium"
          >
            Siswa
          </button>
          <button
            type="button"
            @click="handleQuickLogin('GURU_PENGAJAR')"
            class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-primary hover:border-primary/40 bg-gray-50/50 hover:bg-red-50/30 transition-colors text-[11px] font-medium"
          >
            Guru Pengajar
          </button>
          <button
            type="button"
            @click="handleQuickLogin('GURU_PEMBIMBING')"
            class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-primary hover:border-primary/40 bg-gray-50/50 hover:bg-red-50/30 transition-colors text-[11px] font-medium"
          >
            Guru Pembimbing
          </button>
          <button
            type="button"
            @click="handleQuickLogin('SUPERADMIN')"
            class="col-span-2 py-2 px-3 rounded-xl border border-red-200 bg-red-50/80 hover:bg-red-100 text-primary font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
          >
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <span>Masuk Cepat: Super Administrator (Semua Hak Akses)</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Book, Eye, EyeOff } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false,
})

const authStore = useAuthStore()
const email = ref('rina.safitri@gmail.com')
const password = ref('password123')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  errorMessage.value = ''
  isLoading.value = true
  try {
    await authStore.login(email.value, password.value)
    navigateTo('/dashboard')
  } catch (err: any) {
    errorMessage.value = err?.data?.message || 'Email atau kata sandi salah.'
  } finally {
    isLoading.value = false
  }
}

async function handleQuickLogin(role: string) {
  errorMessage.value = ''
  isLoading.value = true
  try {
    await authStore.quickLogin(role)
    navigateTo('/dashboard')
  } catch (err: any) {
    errorMessage.value = err?.data?.message || 'Gagal login cepat.'
  } finally {
    isLoading.value = false
  }
}
</script>
