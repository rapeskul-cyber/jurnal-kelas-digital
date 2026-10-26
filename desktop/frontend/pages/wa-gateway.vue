<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="WhatsApp Gateway & Siaran Pengumuman"
      subtitle="Kelola koneksi bot WhatsApp sekolah, uji coba pengiriman pesan, dan sebarkan siaran pengumuman cepat"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Alerts -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600 flex-shrink-0" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-700 hover:text-rose-900 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT (5 COLS): KONEKSI WHATSAPP GATEWAY -->
        <div class="lg:col-span-5 space-y-6">
          <!-- Connection Status Card -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                  <MessageSquare class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-900">Status server WA</h3>
                </div>
              </div>

              <!-- Status Badge -->
              <span
                class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"
                :class="waStatus === 'connected' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200'"
              >
                <span
                  class="w-2 h-2 rounded-full"
                  :class="waStatus === 'connected' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"
                ></span>
                {{ waStatus === 'connected' ? 'Terhubung' : 'Offline' }}
              </span>
            </div>

            <!-- Bot Details or Pairing Form -->
            <div class="space-y-4 text-xs">
              <div v-if="waStatus === 'connected'" class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
                <div class="flex justify-between text-gray-700">
                  <span class="font-medium">Nomor Bot:</span>
                  <span class="font-bold text-gray-900 font-mono">{{ waNumber || '6281234567890' }}</span>
                </div>
                <div class="flex justify-between text-gray-700">
                  <span class="font-medium">Koneksi Sesi:</span>
                  <span class="font-bold text-emerald-600">Aktif & Siap Kirim</span>
                </div>
                <div class="pt-2">
                  <button
                    type="button"
                    @click="handleLogoutWa"
                    class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl border border-rose-200 transition text-center cursor-pointer"
                  >
                    Putuskan Sambungan
                  </button>
                </div>
              </div>

              <div v-else class="space-y-3">
                <p class="text-gray-500 text-[11px] leading-relaxed">
                  Hubungkan nomor WhatsApp resmi sekolah untuk otomatisasi notifikasi presensi harian dan siaran pengumuman kepada wali murid dan guru.
                </p>

                <div>
                  <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-gray-700">Nomor WhatsApp Server</label>
                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                      Pairing Khusus: JURNAL 71
                    </span>
                  </div>
                  <div class="flex gap-2">
                    <input
                      v-model="pairPhone"
                      type="text"
                      placeholder="Contoh: 6281234567890"
                      class="flex-1 px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    />
                    <button
                      type="button"
                      @click="handleRequestPairing"
                      :disabled="pairingLoading"
                      class="px-3.5 py-2 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition text-xs cursor-pointer disabled:opacity-50"
                    >
                      {{ pairingLoading ? 'Memproses...' : 'Pairing Code' }}
                    </button>
                  </div>
                </div>

                <!-- Display Pairing Code if requested -->
                <div v-if="pairingCode" class="p-4 bg-emerald-50/70 rounded-xl border border-emerald-200 text-center space-y-1.5">
                  <p class="text-[11px] text-emerald-800 font-semibold">Buka WhatsApp HP ➔ Perangkat Tertaut ➔ Masukkan Kode:</p>
                  <p class="text-2xl font-mono font-black text-emerald-700 tracking-widest select-all py-1">{{ pairingCode }}</p>
                  <p class="text-[10px] text-emerald-600">Kode pairing resmi: <strong>JURNAL 71</strong> (Ketik di WhatsApp HP: <strong>JURN-AL71</strong>)</p>
                </div>
              </div>

              <!-- Button Refresh Status -->
              <button
                type="button"
                @click="checkWaStatus"
                class="w-full py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded-xl border border-gray-200 transition flex items-center justify-center gap-2 cursor-pointer"
              >
                <RefreshCw class="w-4 h-4 text-gray-500" :class="{ 'animate-spin': checkingStatus }" />
                <span>Cek Ulang Status Koneksi</span>
              </button>
            </div>
          </div>

          <!-- Uji Coba Kirim Pesan WA Card -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
              <Send class="w-4 h-4 text-primary" />
              <span>Uji Coba Kirim Pesan Cepat</span>
            </h3>

            <form @submit.prevent="handleTestSend" class="space-y-3 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">Nomor Tujuan Uji Coba</label>
                <input
                  v-model="testNumber"
                  type="text"
                  required
                  placeholder="Contoh: 6281299998888"
                  class="w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">Pesan Uji Coba</label>
                <textarea
                  v-model="testMessage"
                  rows="2"
                  required
                  placeholder="Halo, ini adalah pesan uji coba dari sistem Jurnal Kelas Digital."
                  class="w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                ></textarea>
              </div>

              <button
                type="submit"
                :disabled="sendingTest"
                class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition cursor-pointer disabled:opacity-50"
              >
                {{ sendingTest ? 'Mengirim...' : 'Kirim Pesan Uji Coba' }}
              </button>
            </form>
          </div>
        </div>

        <!-- RIGHT (7 COLS): SIARAN PENGUMUMAN SIMPEL -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <Radio class="w-4 h-4 text-primary" />
                  <span>Buat Siaran Pengumuman Cepat</span>
                </h3>
              </div>
            </div>

            <form @submit.prevent="handleSendBroadcast" class="space-y-4 text-xs">
              <!-- Target Audiens -->
              <div>
                <label class="block font-bold text-gray-700 mb-1.5">Target Audiens Penerima *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                  <button
                    type="button"
                    @click="broadcastForm.target_role = 'ALL'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.target_role === 'ALL' ? 'border-primary bg-red-50 text-primary font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Semua Pengguna
                  </button>
                  <button
                    type="button"
                    @click="broadcastForm.target_role = 'GURU_PEMBIMBING'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.target_role === 'GURU_PEMBIMBING' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Wali Kelas
                  </button>
                  <button
                    type="button"
                    @click="broadcastForm.target_role = 'GURU_PENGAJAR'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.target_role === 'GURU_PENGAJAR' ? 'border-blue-600 bg-blue-50 text-blue-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Guru Pengajar
                  </button>
                  <button
                    type="button"
                    @click="broadcastForm.target_role = 'SISWA'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.target_role === 'SISWA' ? 'border-purple-600 bg-purple-50 text-purple-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Siswa & Wali
                  </button>
                </div>
              </div>

              <!-- Saluran Pengiriman -->
              <div>
                <label class="block font-bold text-gray-700 mb-1.5">Saluran Pengiriman *</label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    type="button"
                    @click="broadcastForm.channel = 'ALL'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.channel === 'ALL' ? 'border-primary bg-red-50 text-primary font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Web + WhatsApp
                  </button>
                  <button
                    type="button"
                    @click="broadcastForm.channel = 'IN_APP'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.channel === 'IN_APP' ? 'border-primary bg-red-50 text-primary font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    Notifikasi Web
                  </button>
                  <button
                    type="button"
                    @click="broadcastForm.channel = 'WHATSAPP'"
                    class="p-2.5 rounded-xl border text-center transition cursor-pointer"
                    :class="broadcastForm.channel === 'WHATSAPP' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
                  >
                    WhatsApp Bot
                  </button>
                </div>
              </div>

              <!-- Judul Siaran -->
              <div>
                <label class="block font-bold text-gray-700 mb-1">Judul Pengumuman *</label>
                <input
                  v-model="broadcastForm.title"
                  type="text"
                  required
                  placeholder="Contoh: Pemberitahuan Libur Penilaian Akhir Semester"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <!-- Isi Pesan -->
              <div>
                <label class="block font-bold text-gray-700 mb-1">Isi Pesan Siaran *</label>
                <textarea
                  v-model="broadcastForm.message"
                  rows="4"
                  required
                  placeholder="Tuliskan detail pengumuman yang ingin disampaikan secara lengkap..."
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                ></textarea>
              </div>

              <div class="pt-2 flex justify-end">
                <button
                  type="submit"
                  :disabled="broadcasting"
                  class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition flex items-center gap-2 shadow-xs cursor-pointer disabled:opacity-50"
                >
                  <Send class="w-4 h-4" />
                  <span>{{ broadcasting ? 'Mengirim Siaran...' : 'Kirim Siaran Sekarang' }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Riwayat Siaran Terakhir -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Riwayat Siaran Terakhir</h3>

            <div v-if="loadingBroadcasts" class="text-center py-6 text-gray-400 text-xs">
              Memuat riwayat siaran...
            </div>

            <div v-else-if="!broadcastHistory.length" class="text-center py-6 text-gray-400 text-xs">
              Belum ada siaran pengumuman yang dikirim.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="b in broadcastHistory"
                :key="b.id"
                class="p-3.5 bg-gray-50 rounded-xl border border-gray-150 space-y-1.5 text-xs"
              >
                <div class="flex items-center justify-between">
                  <h4 class="font-bold text-gray-900">{{ b.title }}</h4>
                  <span class="text-[10px] text-gray-400 font-mono">{{ b.created_at ? new Date(b.created_at).toLocaleDateString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-' }}</span>
                </div>
                <p class="text-gray-600 text-[11px] leading-relaxed">{{ b.message }}</p>
                <div class="flex items-center gap-3 text-[10px] text-gray-500 pt-1 border-t border-gray-200">
                  <span>Target: <strong class="text-gray-700">{{ b.target_role }}</strong></span>
                  <span>•</span>
                  <span>Saluran: <strong class="text-gray-700">{{ b.channel }}</strong></span>
                  <span>•</span>
                  <span>Penerima: <strong class="text-emerald-600">{{ b.recipients_count || 0 }} Akun</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  MessageSquare,
  Radio,
  Send,
  RefreshCw,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const successMsg = ref('')
const errorMsg = ref('')

// WA States
const waStatus = ref('disconnected')
const waNumber = ref('')
const pairPhone = ref('6281234567890')
const pairingCode = ref('')
const pairingLoading = ref(false)
const checkingStatus = ref(false)

// Test Message States
const testNumber = ref('')
const testMessage = ref('Halo, ini uji coba pesan WhatsApp dari sistem Jurnal Kelas Digital.')
const sendingTest = ref(false)

// Broadcast States
const broadcasting = ref(false)
const loadingBroadcasts = ref(false)
const broadcastHistory = ref<any[]>([])

const broadcastForm = ref({
  title: '',
  message: '',
  target_role: 'ALL',
  channel: 'ALL',
})

async function checkWaStatus() {
  if (!authStore.token) return
  checkingStatus.value = true
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/wa/status`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    waStatus.value = res.connected ? 'connected' : 'disconnected'
    waNumber.value = res.number || ''
  } catch (e) {
    waStatus.value = 'disconnected'
  } finally {
    checkingStatus.value = false
  }
}

async function handleRequestPairing() {
  if (!pairPhone.value) {
    errorMsg.value = 'Masukkan nomor HP untuk pairing WhatsApp.'
    return
  }
  pairingLoading.value = true
  errorMsg.value = ''
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/wa/pairing-code`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        phone: pairPhone.value,
        custom_code: 'JURNAL71'
      }
    })
    const raw = res.pairing_code || res.code || (res.data && res.data.code) || 'JURNAL71'
    pairingCode.value = raw.length === 8 ? `${raw.slice(0, 4)}-${raw.slice(4)}` : raw
    successMsg.value = 'Kode pairing JURNAL 71 siap. Masukkan di WhatsApp HP Anda.'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal meminta kode pairing.'
  } finally {
    pairingLoading.value = false
  }
}

async function handleLogoutWa() {
  if (!confirm('Putuskan koneksi bot WhatsApp?')) return
  try {
    await $fetch(`${authStore.apiBase}/wa/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    waStatus.value = 'disconnected'
    successMsg.value = 'Koneksi WhatsApp berhasil diputuskan.'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memutuskan WhatsApp.'
  }
}

async function handleTestSend() {
  if (!testNumber.value) {
    errorMsg.value = 'Masukkan nomor tujuan tes.'
    return
  }
  sendingTest.value = true
  errorMsg.value = ''
  try {
    // We send test using broadcast endpoint or test endpoint
    const res = await $fetch<any>(`${authStore.apiBase}/system/broadcasts`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        title: 'Uji Coba Pengiriman Pesan',
        message: testMessage.value,
        target_role: 'ALL',
        channel: 'WHATSAPP',
      }
    })
    successMsg.value = 'Pesan uji coba berhasil dikirim ke server gateway.'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengirim pesan uji coba.'
  } finally {
    sendingTest.value = false
  }
}

async function fetchBroadcasts() {
  if (!authStore.token) return
  loadingBroadcasts.value = true
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/system/broadcasts`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    broadcastHistory.value = res || []
  } catch (e) {
    console.warn(e)
  } finally {
    loadingBroadcasts.value = false
  }
}

async function handleSendBroadcast() {
  if (!broadcastForm.value.title || !broadcastForm.value.message) {
    errorMsg.value = 'Judul dan pesan siaran wajib diisi.'
    return
  }
  broadcasting.value = true
  errorMsg.value = ''
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/system/broadcasts`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: broadcastForm.value
    })
    successMsg.value = res.message || 'Siaran pengumuman berhasil disebarkan.'
    broadcastForm.value.title = ''
    broadcastForm.value.message = ''
    fetchBroadcasts()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyebarkan siaran pengumuman.'
  } finally {
    broadcasting.value = false
  }
}

onMounted(() => {
  checkWaStatus()
  fetchBroadcasts()
})
</script>
