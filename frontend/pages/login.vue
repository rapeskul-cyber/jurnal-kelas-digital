<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-50/60 flex flex-col justify-center items-center p-3.5 py-6 sm:py-8">
    <!-- Auth Card -->
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-md text-center my-auto">
      <!-- Header -->
      <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1.5 font-medium">Jurnal Kegiatan Digital</p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left flex items-center gap-2">
        <span class="text-red-500">⚠</span>
        {{ errorMessage }}
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-4 text-left">
        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Alamat Email Akun
          </label>
          <input
            v-model="email"
            type="email"
            required
            id="login-email"
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
              id="login-password"
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

        <!-- Point 2: Cloudflare Turnstile Anti-Bot Widget (Invisible) -->
        <div v-if="turnstileSiteKey" class="turnstile-invisible-wrapper" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none; width: 0; height: 0; overflow: hidden;">
          <div ref="turnstileContainer" id="turnstile-widget"></div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          id="login-submit-btn"
          class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover active:scale-[0.99] text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 mt-1 disabled:opacity-70 cursor-pointer"
        >
          <span v-if="isLoading" class="flex items-center gap-2">
            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            Memproses...
          </span>
          <span v-else>Masuk Ke Dashboard</span>
        </button>

        <!-- Forgot Password Link & Back to Home -->
        <div class="flex items-center justify-between pt-1">
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
import {
  Eye,
  EyeOff,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useAppConfigStore } from '~/stores/appConfig'

definePageMeta({
  layout: false,
})

const authStore = useAuthStore()
const appConfig = useAppConfigStore()
const runtimeConfig = useRuntimeConfig()
const turnstileSiteKey = computed(() => runtimeConfig.public.turnstileSiteKey || '')

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const turnstileContainer = ref<HTMLElement | null>(null)
const turnstileToken = ref<string>('')
const turnstileWidgetId = ref<string | null>(null)

function renderTurnstile() {
  if (typeof window === 'undefined' || !turnstileSiteKey.value) return
  const win = window as any
  if (win.turnstile && turnstileContainer.value && !turnstileWidgetId.value) {
    try {
      turnstileWidgetId.value = win.turnstile.render(turnstileContainer.value, {
        sitekey: turnstileSiteKey.value,
        size: 'invisible',
        callback: (token: string) => {
          turnstileToken.value = token
        },
        'expired-callback': () => {
          turnstileToken.value = ''
        },
        'error-callback': () => {
          turnstileToken.value = ''
        },
      })
    } catch (e) {
      console.warn('Turnstile render note:', e)
    }
  }
}

onMounted(() => {
  appConfig.fetchConfig()

  if (turnstileSiteKey.value) {
    // Polling Turnstile script readiness
    const interval = setInterval(() => {
      const win = window as any
      if (win.turnstile && turnstileContainer.value) {
        renderTurnstile()
        clearInterval(interval)
      }
    }, 250)
    setTimeout(() => clearInterval(interval), 6000)
  }
})

async function handleLogin() {
  errorMessage.value = ''
  isLoading.value = true
  try {
    // If Turnstile is active but token is not yet ready, attempt execute
    if (turnstileSiteKey.value && !turnstileToken.value && typeof window !== 'undefined') {
      const win = window as any
      if (win.turnstile && turnstileWidgetId.value) {
        try {
          win.turnstile.execute(turnstileWidgetId.value)
          let waitCycles = 0
          while (!turnstileToken.value && waitCycles < 15) {
            await new Promise((r) => setTimeout(r, 100))
            waitCycles++
          }
        } catch (e) {
          console.warn('Turnstile execution note:', e)
        }
      }
    }

    await authStore.login(email.value, password.value, turnstileToken.value)
    navigateTo('/dashboard')
  } catch (err: any) {
    errorMessage.value = err?.data?.message || 'Email atau kata sandi salah.'
    // Reset Turnstile on error
    if (typeof window !== 'undefined') {
      const win = window as any
      if (win.turnstile && turnstileWidgetId.value) {
        try {
          win.turnstile.reset(turnstileWidgetId.value)
        } catch (e) {}
        turnstileToken.value = ''
      }
    }
  } finally {
    isLoading.value = false
  }
}
</script>
