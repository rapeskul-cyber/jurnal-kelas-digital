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
      <p class="text-xs text-gray-500 mt-1 mb-8">Sistem Presensi Mandiri & Jurnal Kegiatan Digital</p>

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

        <!-- Forgot Password Link & Back to Home -->
        <div class="flex items-center justify-between pt-2">
          <NuxtLink
            to="/"
            class="inline-block text-xs text-gray-500 hover:text-gray-800 font-medium py-1.5 transition cursor-pointer"
          >
            ← Kembali ke Beranda
          </NuxtLink>
          <NuxtLink
            to="/forgot-password"
            class="inline-block text-xs text-primary hover:underline font-semibold py-1.5 px-3 rounded-lg hover:bg-blue-50/50 transition cursor-pointer"
          >
            Lupa kata sandi?
          </NuxtLink>
        </div>
      </form>
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
const email = ref('')
const password = ref('')
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
</script>
