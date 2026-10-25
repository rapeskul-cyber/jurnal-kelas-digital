<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Pusat Siaran & WhatsApp Gateway"
      subtitle="Kirim pengumuman massal ke seluruh civitas sekolah dan pantau integrasi WhatsApp Gateway"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Alerts -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT COLUMN: Formulir Siaran Baru & Riwayat (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Form Card -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-50 text-primary flex items-center justify-center">
                  <Radio class="w-4 h-4" />
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-900">Buat Siaran / Pengumuman Baru</h3>
                  <p class="text-xs text-gray-400">Pesan akan diteruskan ke notifikasi aplikasi dan/atau WhatsApp penerima</p>
                </div>
              </div>
            </div>

            <form @submit.prevent="handleSendBroadcast" class="space-y-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">Judul Pengumuman</label>
                <input
                  v-model="form.title"
                  type="text"
                  placeholder="Contoh: Pemberitahuan Pelaksanaan Ujian Tengah Semester & Libur Nasional"
                  required
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs"
                />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-semibold text-gray-700 mb-1">Target Sasaran Penerima</label>
                  <select
                    v-model="form.target_role"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white text-xs cursor-pointer"
                  >
                    <option value="ALL">Semua Pengguna (Siswa & Guru)</option>
                    <option value="SISWA">Khusus Siswa & Sekretaris</option>
                    <option value="GURU_PENGAJAR">Khusus Guru Pengajar</option>
                    <option value="GURU_PEMBIMBING">Khusus Guru Pembimbing / Wali</option>
                  </select>
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 mb-1">Jalur Pengiriman (Channel)</label>
                  <select
                    v-model="form.channel"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white text-xs cursor-pointer"
                  >
                    <option value="ALL">Multi-Channel (In-App & WhatsApp)</option>
                    <option value="IN_APP">Notifikasi Dalam Aplikasi Saja</option>
                    <option value="WHATSAPP">WhatsApp Gateway Saja</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">Isi Pesan Pengumuman</label>
                <textarea
                  v-model="form.message"
                  rows="4"
                  placeholder="Tuliskan detail pengumuman yang ingin disampaikan secara lengkap..."
                  required
                  class="w-full p-3.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs leading-relaxed"
                ></textarea>
              </div>

              <!-- Preview Format WhatsApp -->
              <div v-if="form.channel !== 'IN_APP'" class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-1">
                <p class="font-bold text-emerald-900 flex items-center gap-1.5 text-[11px]">
                  <span>Pratinjau Pesan WhatsApp:</span>
                </p>
                <div class="p-2.5 bg-white rounded-lg border border-emerald-100 font-mono text-[11px] text-gray-800 whitespace-pre-wrap">
*📢 [PENGUMUMAN SEKOLAH]*

*{{ form.title || '(Judul Pengumuman)' }}*

{{ form.message || '(Isi pesan pengumuman...)' }}

_Pesan otomatis Sistem Jurnal Digital_
                </div>
              </div>

              <div class="pt-2 flex justify-end">
                <button
                  type="submit"
                  :disabled="sending || !form.title || !form.message"
                  class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <Send class="w-3.5 h-3.5" />
                  <span>{{ sending ? 'Menyiarkan Pesan...' : 'Siarkan Pengumuman Sekarang' }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- History Table Card -->
          <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-gray-100">
              <h3 class="text-sm font-bold text-gray-900">Riwayat Siaran Terkirim</h3>
              <p class="text-xs text-gray-400 mt-0.5">Catatan seluruh pengumuman resmi yang telah didistribusikan sistem</p>
            </div>

            <div v-if="loadingBroadcasts" class="py-12 text-center text-gray-400 text-xs">
              Memuat riwayat siaran...
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4">Judul Pengumuman</th>
                    <th class="py-3 px-4">Sasaran</th>
                    <th class="py-3 px-4 text-center">Penerima</th>
                    <th class="py-3 px-4 text-center">Jalur</th>
                    <th class="py-3 px-4">Waktu Kirim</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(b, idx) in broadcasts" :key="b.id" class="hover:bg-gray-50/50 transition">
                    <td class="py-3 px-4 text-center text-gray-400">{{ idx + 1 }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900">{{ b.title }}</td>
                    <td class="py-3 px-4 text-gray-600 font-medium">{{ b.target_role }}</td>
                    <td class="py-3 px-4 text-center font-bold text-primary">{{ b.recipients_count }} User</td>
                    <td class="py-3 px-4 text-center">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                        {{ b.channel }}
                      </span>
                    </td>
                    <td class="py-3 px-4 text-gray-400 font-mono text-[11px]">{{ formatDate(b.created_at) }}</td>
                  </tr>
                  <tr v-if="!broadcasts.length">
                    <td colspan="6" class="py-8 text-center text-gray-400">
                      Belum ada riwayat siaran yang dikirim.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: WhatsApp Gateway Management (1 Col) -->
        <div class="space-y-6">
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900">WhatsApp Gateway Sentral</h3>
                <p class="text-xs text-gray-400 mt-0.5">Integrasi Fonte / Baileys lokal</p>
              </div>
              <span
                :class="(waStatus.status === 'connected' || waStatus.connected) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                class="text-[10px] font-bold px-2.5 py-1 rounded-full border"
              >
                {{ (waStatus.status === 'connected' || waStatus.connected) ? '● Terhubung' : '○ Standby / Disconnected' }}
              </span>
            </div>

            <!-- If Not Connected: Pairing Code Input -->
            <div v-if="!waStatus.connected && waStatus.status !== 'connected'" class="space-y-3 text-xs">
              <p class="text-gray-600 leading-relaxed">
                Tautkan nomor WhatsApp sekolah untuk pengiriman kode OTP reset kata sandi dan broadcast pengumuman secara otomatis tanpa biaya pihak ketiga.
              </p>
              <div>
                <label class="block font-semibold text-gray-700 mb-1">Nomor WhatsApp Bot</label>
                <div class="flex gap-2">
                  <input
                    v-model="waPhoneInput"
                    type="text"
                    placeholder="Contoh: 088298674462"
                    class="flex-1 px-3.5 py-2 rounded-xl border border-gray-200 outline-none text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  />
                  <button
                    type="button"
                    :disabled="requestingWa || !waPhoneInput"
                    @click="requestWaPairing"
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition disabled:opacity-50 cursor-pointer"
                  >
                    {{ requestingWa ? 'Meminta...' : 'Pairing' }}
                  </button>
                </div>
              </div>

              <!-- 8 Digit Pairing Code Box -->
              <div v-if="pairingCode" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-center space-y-1.5 animate-in fade-in">
                <p class="text-xs text-emerald-800 font-semibold">Kode Pairing WhatsApp Anda:</p>
                <div class="text-2xl font-mono font-black tracking-widest text-emerald-700 select-all py-1">
                  {{ pairingCode }}
                </div>
                <p class="text-[10px] text-emerald-600">
                  Buka WhatsApp di HP ➔ <strong>Perangkat Tertaut</strong> ➔ <strong>Tautkan dengan nomor telepon</strong> ➔ Ketik kode di atas.
                </p>
              </div>
            </div>

            <!-- If Connected: Info & Disconnect Button -->
            <div v-else class="space-y-4 text-xs">
              <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-1">
                <p class="text-emerald-900 font-bold">Bot Aktif Melayani:</p>
                <p class="font-mono text-emerald-700">{{ waStatus.user?.id || 'Nomor Bot Terpasang' }}</p>
                <p class="text-[10px] text-emerald-600">Siap mendistribusikan OTP dan pengumuman instan.</p>
              </div>

              <button
                type="button"
                @click="logoutWa"
                class="w-full py-2 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 font-bold rounded-xl transition text-xs cursor-pointer"
              >
                Putuskan Koneksi Perangkat
              </button>
            </div>

            <!-- Test Send WhatsApp Box -->
            <div class="pt-3 border-t border-gray-100 space-y-2 text-xs">
              <p class="font-bold text-gray-800">Uji Coba Kirim Pesan:</p>
              <div class="flex gap-2">
                <input
                  v-model="testPhoneInput"
                  type="text"
                  placeholder="Nomor tujuan (08...)"
                  class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 text-xs outline-none"
                />
                <button
                  type="button"
                  :disabled="sendingTest || !testPhoneInput"
                  @click="handleSendTestWa"
                  class="px-3 py-1.5 bg-gray-900 hover:bg-black text-white font-semibold rounded-lg text-xs transition cursor-pointer disabled:opacity-50"
                >
                  {{ sendingTest ? 'Mengirim...' : 'Tes Kirim' }}
                </button>
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
  Radio,
  Send,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'

const successMsg = ref('')
const errorMsg = ref('')
const sending = ref(false)
const loadingBroadcasts = ref(false)

const form = ref({
  title: '',
  target_role: 'ALL',
  channel: 'ALL',
  message: '',
})

const broadcasts = ref<any[]>([])

// WhatsApp Gateway State
const waStatus = ref<any>({ status: 'disconnected', connected: false })
const waPhoneInput = ref('')
const pairingCode = ref('')
const requestingWa = ref(false)

const testPhoneInput = ref('')
const sendingTest = ref(false)

let pollInterval: any = null

async function fetchWaStatus() {
  try {
    const res: any = await $fetch(`${apiBase}/wa-gateway/status`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res) {
      waStatus.value = res.data || res
      if (res.connected || res.status === 'connected') {
        pairingCode.value = ''
      }
    }
  } catch (e) {
    waStatus.value = { status: 'disconnected', connected: false }
  }
}

async function requestWaPairing() {
  if (!waPhoneInput.value) return
  requestingWa.value = true
  pairingCode.value = ''
  try {
    const res: any = await $fetch(`${apiBase}/wa-gateway/pairing-code`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { phone: waPhoneInput.value }
    })
    if (res && (res.code || (res.data && res.data.code))) {
      pairingCode.value = res.code || res.data.code
      successMsg.value = 'Kode pairing berhasil didapatkan. Silakan input pada WhatsApp HP Anda.'
    } else if (res && res.message) {
      alert(res.message)
    }
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal meminta kode pairing WhatsApp'
  } finally {
    requestingWa.value = false
    fetchWaStatus()
  }
}

async function logoutWa() {
  if (!confirm('Putuskan koneksi bot WhatsApp ini?')) return
  try {
    await $fetch(`${apiBase}/wa-gateway/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    pairingCode.value = ''
    successMsg.value = 'Koneksi WhatsApp Gateway berhasil diputuskan.'
    fetchWaStatus()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memutuskan WhatsApp'
  }
}

async function handleSendTestWa() {
  if (!testPhoneInput.value) return
  sendingTest.value = true
  try {
    const res: any = await $fetch(`${apiBase}/system/broadcasts`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        title: 'Uji Coba Sistem WhatsApp',
        message: 'Ini adalah pesan uji coba konektivitas sistem WhatsApp Gateway Jurnal Digital. Jika Anda menerima pesan ini, gateway berfungsi normal.',
        target_role: 'ALL',
        channel: 'WHATSAPP',
      }
    })
    successMsg.value = 'Uji coba pesan WhatsApp berhasil diproses.'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengirim pesan uji coba.'
  } finally {
    sendingTest.value = false
  }
}

async function fetchBroadcasts() {
  loadingBroadcasts.value = true
  try {
    const res: any = await $fetch(`${apiBase}/system/broadcasts`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    broadcasts.value = res || []
  } catch (e) {
    console.warn(e)
  } finally {
    loadingBroadcasts.value = false
  }
}

async function handleSendBroadcast() {
  sending.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res: any = await $fetch(`${apiBase}/system/broadcasts`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: form.value,
    })
    successMsg.value = res.message || 'Pengumuman berhasil disiarkan!'
    form.value = {
      title: '',
      target_role: 'ALL',
      channel: 'ALL',
      message: '',
    }
    fetchBroadcasts()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyiarkan pengumuman.'
  } finally {
    sending.value = false
  }
}

function formatDate(iso: string) {
  if (!iso) return '-'
  const d = new Date(iso)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchWaStatus()
  fetchBroadcasts()
  pollInterval = setInterval(fetchWaStatus, 4000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
