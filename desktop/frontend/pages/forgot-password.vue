<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-3xl p-8 sm:p-10 shadow-sm text-center">
      <!-- Icon Lock / Check -->
      <div
        v-if="currentStep !== 4"
        class="w-12 h-12 bg-red-50 text-primary rounded-2xl flex items-center justify-center mx-auto mb-4"
      >
        <Lock class="w-6 h-6" />
      </div>
      <div
        v-else
        class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4"
      >
        <CheckCircle2 class="w-7 h-7" />
      </div>

      <h1 class="text-xl font-bold text-gray-900 tracking-tight">
        {{ currentStep === 4 ? 'Kata Sandi Berhasil Diperbarui!' : 'Pemulihan Kata Sandi' }}
      </h1>
      <p class="text-xs text-gray-500 mt-1 mb-6">
        {{ currentStep === 4 ? 'Kata sandi akun Anda telah diperbarui. Silakan gunakan sandi baru untuk masuk.' : 'Verifikasi akun email & reset kata sandi via WhatsApp' }}
      </p>

      <!-- Stepper (Steps 1, 2, 3) -->
      <div v-if="currentStep < 4" class="flex items-center justify-center gap-3 mb-8">
        <div
          v-for="s in [1, 2, 3]"
          :key="s"
          class="flex items-center gap-3"
        >
          <div
            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors"
            :class="[
              s === currentStep
                ? 'bg-primary text-white'
                : s < currentStep
                ? 'bg-red-100 text-primary'
                : 'bg-gray-100 text-gray-400'
            ]"
          >
            <Check v-if="s < currentStep" class="w-3.5 h-3.5" />
            <span v-else>{{ s }}</span>
          </div>
          <div v-if="s < 3" class="w-8 h-0.5" :class="s < currentStep ? 'bg-primary' : 'bg-gray-200'"></div>
        </div>
      </div>

      <!-- Error / Success Notice -->
      <div v-if="message" class="mb-5 p-3 rounded-xl text-xs text-left" :class="isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
        {{ message }}
      </div>

      <!-- STEP 1: Email Form -->
      <form v-if="currentStep === 1" @submit.prevent="handleStep1" class="space-y-5 text-left">
        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Alamat Email Akun
          </label>
          <div class="relative">
            <input
              v-model="email"
              type="email"
              required
              placeholder="sekretaris@gmail.com"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>
          <p class="text-[11px] text-gray-400 mt-2">
            Kami akan mendeteksi nomor WhatsApp yang terhubung dengan email ini untuk mengirimkan kode OTP pengaman.
          </p>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2"
        >
          <span>{{ isLoading ? 'Memproses...' : 'Verifikasi Akun & Kirim OTP →' }}</span>
        </button>

        <div class="text-center pt-2">
          <NuxtLink to="/login" class="text-xs text-gray-500 hover:text-gray-900">
            ← Kembali ke Halaman Login
          </NuxtLink>
        </div>
      </form>

      <!-- STEP 2: OTP Verification -->
      <form v-if="currentStep === 2" @submit.prevent="handleStep2" class="space-y-5 text-left">
        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 text-xs space-y-1">
          <p class="text-gray-500">Akun Email: <span class="font-semibold text-gray-800">{{ maskedEmail }}</span></p>
          <p class="text-gray-500">WhatsApp Tujuan: <span class="font-semibold text-gray-800">{{ maskedPhone }}</span></p>
        </div>

        <!-- Helper preview OTP saat gateway offline / testing -->
        <div v-if="otpPreview" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
          <p class="font-bold flex items-center justify-between">
            <span>🔔 Mode Simulasi / OTP Cepat:</span>
            <button
              type="button"
              @click="otp = otpPreview"
              class="text-[11px] underline text-primary font-semibold hover:text-primary-hover cursor-pointer"
            >
              Isi Otomatis
            </button>
          </p>
          <p>Gateway WA belum terhubung ke nomor riil. Masukkan kode uji coba: <strong class="font-mono text-sm text-primary tracking-wider">{{ otpPreview }}</strong></p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              Kode Verifikasi
            </label>
            <button type="button" @click="currentStep = 1" class="text-[11px] text-primary hover:underline font-semibold">
              Ganti Email
            </button>
          </div>
          <input
            v-model="otp"
            type="text"
            maxlength="6"
            required
            placeholder="000000"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-center tracking-widest text-lg font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
          <div class="flex items-center justify-between mt-2 text-[11px] text-gray-400">
            <span>Kode berlaku selama 5 menit.</span>
            <button
              type="button"
              :disabled="cooldown > 0"
              @click="handleStep1"
              class="font-semibold text-primary disabled:text-gray-400"
            >
              {{ cooldown > 0 ? `Kirim ulang (${cooldown}s)` : 'Kirim ulang OTP' }}
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2"
        >
          <span>{{ isLoading ? 'Memeriksa...' : 'Verifikasi Kode OTP' }}</span>
        </button>

        <div class="text-center pt-2">
          <NuxtLink to="/login" class="text-xs text-gray-500 hover:text-gray-900">
            ← Kembali ke Halaman Login
          </NuxtLink>
        </div>
      </form>

      <!-- STEP 3: New Password -->
      <form v-if="currentStep === 3" @submit.prevent="handleStep3" class="space-y-4 text-left">
        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Kata Sandi Baru
          </label>
          <input
            v-model="newPassword"
            type="password"
            minlength="8"
            required
            placeholder="Minimal 8 karakter"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </div>

        <div>
          <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
            Konfirmasi Kata Sandi Baru
          </label>
          <input
            v-model="newPasswordConfirmation"
            type="password"
            minlength="8"
            required
            placeholder="Ulangi kata sandi baru"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 mt-2"
        >
          <span>{{ isLoading ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}</span>
        </button>

        <div class="text-center pt-2">
          <NuxtLink to="/login" class="text-xs text-gray-500 hover:text-gray-900">
            ← Kembali ke Halaman Login
          </NuxtLink>
        </div>
      </form>

      <!-- STEP 4: Success Screen -->
      <div v-if="currentStep === 4" class="space-y-5 pt-2">
        <NuxtLink
          to="/login"
          class="block w-full py-3.5 px-4 bg-primary hover:bg-primary-hover text-white rounded-xl text-sm font-semibold transition-all shadow-sm text-center"
        >
          Masuk ke Halaman Login →
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Lock, Check, CheckCircle2 } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false,
})

const authStore = useAuthStore()
const currentStep = ref(1)
const email = ref('rina.safitri@gmail.com')
const maskedEmail = ref('')
const maskedPhone = ref('')
const otp = ref('')
const otpPreview = ref<string | null>(null)
const newPassword = ref('')
const newPasswordConfirmation = ref('')
const cooldown = ref(0)
const isLoading = ref(false)
const message = ref('')
const isError = ref(false)
let timer: any = null

function startCooldown(sec: number) {
  cooldown.value = sec
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (cooldown.value > 0) cooldown.value--
    else clearInterval(timer)
  }, 1000)
}

async function handleStep1() {
  isLoading.value = true
  message.value = ''
  isError.value = false
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/auth/forgot-password/step-1`, {
      method: 'POST',
      body: { email: email.value }
    })
    maskedEmail.value = res.masked_email
    maskedPhone.value = res.masked_phone
    otpPreview.value = res.otp_preview || null
    startCooldown(res.cooldown || 60)
    currentStep.value = 2
  } catch (e: any) {
    isError.value = true
    message.value = e?.data?.message || 'Gagal mengirim OTP.'
  } finally {
    isLoading.value = false
  }
}

async function handleStep2() {
  isLoading.value = true
  message.value = ''
  isError.value = false
  try {
    await $fetch<any>(`${authStore.apiBase}/auth/forgot-password/step-2`, {
      method: 'POST',
      body: { email: email.value, otp: otp.value }
    })
    currentStep.value = 3
  } catch (e: any) {
    isError.value = true
    message.value = e?.data?.message || 'Kode OTP tidak valid.'
  } finally {
    isLoading.value = false
  }
}

async function handleStep3() {
  if (newPassword.value !== newPasswordConfirmation.value) {
    isError.value = true
    message.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  isLoading.value = true
  message.value = ''
  isError.value = false
  try {
    await $fetch<any>(`${authStore.apiBase}/auth/forgot-password/step-3`, {
      method: 'POST',
      body: {
        email: email.value,
        password: newPassword.value,
        password_confirmation: newPasswordConfirmation.value
      }
    })
    currentStep.value = 4
  } catch (e: any) {
    isError.value = true
    message.value = e?.data?.message || 'Gagal memperbarui kata sandi.'
  } finally {
    isLoading.value = false
  }
}
</script>
