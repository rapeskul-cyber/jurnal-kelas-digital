<template>
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-4 py-4 sticky top-16 z-10">
      <h1 class="text-lg font-bold text-gray-900">Profil Saya</h1>
      <p class="text-xs text-gray-500">Kelola info akun, nomor WhatsApp OTP, & keamanan</p>
    </div>

    <main class="px-4 py-5 space-y-4">
      <!-- Profile Card -->
      <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-red-100 text-primary font-black text-xl flex items-center justify-center ring-4 ring-red-50 flex-shrink-0">
          {{ userInitial }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <h2 class="text-base font-bold text-gray-900 truncate">{{ authStore.user?.name || 'Pengguna' }}</h2>
          </div>
          <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-primary border border-red-100">
            {{ roleLabel }}
          </span>
          <p class="text-xs text-gray-500 mt-1 truncate">{{ authStore.user?.email }}</p>
        </div>
      </div>

      <!-- Quick Info List -->
      <div class="bg-white rounded-2xl border border-gray-150 divide-y divide-gray-100 overflow-hidden text-xs">
        <div v-if="authStore.user?.nip_nis" class="p-3.5 flex justify-between items-center">
          <span class="text-gray-500">NIS/NIP</span>
          <span class="font-semibold text-gray-800">{{ authStore.user.nip_nis }}</span>
        </div>
        <div v-if="authStore.user?.school_class" class="p-3.5 flex justify-between items-center">
          <span class="text-gray-500">Kelas Binaan</span>
          <span class="font-semibold text-gray-800">{{ authStore.user.school_class.name }}</span>
        </div>
        <div class="p-3.5 flex justify-between items-center">
          <span class="text-gray-500">Status Akun</span>
          <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
          </span>
        </div>
      </div>

      <!-- Feedback Messages -->
      <div v-if="successMsg" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
        {{ successMsg }}
      </div>
      <div v-if="errorMsg" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium">
        {{ errorMsg }}
      </div>

      <!-- Form Edit Profil & Kontak WhatsApp -->
      <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs space-y-4">
        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Kontak & Informasi</h3>

        <form @submit.prevent="saveProfile" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Nomor WhatsApp
              <span class="text-[10px] text-gray-400 font-normal">(untuk kirim kode OTP)</span>
            </label>
            <input
              v-model="form.phone_number"
              type="tel"
              placeholder="081234567890"
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <button
            type="submit"
            :disabled="saving"
            class="w-full py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary-dark transition disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : 'Perbarui Profil' }}
          </button>
        </form>
      </div>

      <!-- Ganti Kata Sandi -->
      <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs space-y-4">
        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Keamanan Akun</h3>

        <form @submit.prevent="changePassword" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Saat Ini</label>
            <input
              v-model="passwordForm.current_password"
              type="password"
              required
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
            <input
              v-model="passwordForm.new_password"
              type="password"
              required
              minlength="6"
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
            <input
              v-model="passwordForm.new_password_confirmation"
              type="password"
              required
              minlength="6"
              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-primary"
            />
          </div>

          <button
            type="submit"
            :disabled="changingPassword"
            class="w-full py-2.5 rounded-xl bg-gray-900 text-white text-xs font-bold shadow-xs hover:bg-black transition disabled:opacity-50"
          >
            {{ changingPassword ? 'Memproses...' : 'Ubah Kata Sandi' }}
          </button>
        </form>
      </div>

      <!-- Logout Button -->
      <button
        @click="authStore.logout"
        class="w-full py-3 rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 text-xs font-bold hover:bg-rose-100 transition flex items-center justify-center gap-2"
      >
        Keluar Dari Akun
      </button>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()

const saving = ref(false)
const changingPassword = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const form = ref({
  name: '',
  email: '',
  phone_number: ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const userInitial = computed(() => {
  return authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U'
})

const roleLabel = computed(() => {
  const role = authStore.user?.role
  switch (role) {
    case 'superadmin': return 'Super Admin'
    case 'pembimbing': return 'Guru Pembimbing'
    case 'walas': return 'Wali Kelas'
    case 'guru': return 'Guru Mata Pelajaran'
    case 'ketua_kelas': return 'Ketua Kelas'
    case 'murid': return 'Murid'
    default: return 'Pengguna'
  }
})

onMounted(() => {
  if (authStore.user) {
    form.value.name = authStore.user.name || ''
    form.value.email = authStore.user.email || ''
    form.value.phone_number = authStore.user.phone_number || ''
  }
})

async function saveProfile() {
  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res = await $fetch<any>(`${config.public.apiBase}/auth/profile`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      body: form.value
    })
    successMsg.value = res.message || 'Profil berhasil diperbarui.'
    if (res.user) {
      authStore.setUser(res.user)
    }
  } catch (err: any) {
    errorMsg.value = err.data?.message || 'Gagal memperbarui profil.'
  } finally {
    saving.value = false
  }
}

async function changePassword() {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    errorMsg.value = 'Konfirmasi kata sandi baru tidak cocok.'
    return
  }
  changingPassword.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res = await $fetch<any>(`${config.public.apiBase}/auth/profile`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      body: {
        current_password: passwordForm.value.current_password,
        new_password: passwordForm.value.new_password,
        new_password_confirmation: passwordForm.value.new_password_confirmation
      }
    })
    successMsg.value = res.message || 'Kata sandi berhasil diubah.'
    passwordForm.value.current_password = ''
    passwordForm.value.new_password = ''
    passwordForm.value.new_password_confirmation = ''
  } catch (err: any) {
    errorMsg.value = err.data?.message || 'Gagal mengubah kata sandi.'
  } finally {
    changingPassword.value = false
  }
}
</script>
