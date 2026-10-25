<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Kontrol Presensi & Override Sesi"
      subtitle="Buka kunci sesi presensi terkunci untuk koreksi data dan pantau kehadiran harian seluruh rombel"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Top Filter Bar & Summary -->
      <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <div>
            <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Pilih Tanggal Presensi</label>
            <input
              v-model="selectedDate"
              type="date"
              @change="fetchMonitoring"
              class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer"
            />
          </div>
          <button
            type="button"
            @click="setToday"
            class="mt-4 px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
          >
            Hari Ini
          </button>
        </div>

        <div class="flex items-center gap-2">
          <NuxtLink
            to="/rekap-semester"
            class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs"
          >
            <BarChart2 class="w-4 h-4 text-gray-500" />
            <span>Rekap Semester</span>
          </NuxtLink>
          <button
            @click="fetchMonitoring"
            class="px-3.5 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
            <span>Segarkan Status</span>
          </button>
        </div>
      </div>

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

      <!-- Quick KPI Cards for the Selected Date -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard title="Total Rombel" :value="stats.total_classes + ' Kelas'" subtext="Terdata di Sistem" color="blue" />
        <StatCard title="Sesi Terkunci" :value="stats.locked_classes + ' Sesi'" subtext="Presensi Disahkan" color="red" />
        <StatCard title="Sesi Draf" :value="stats.draft_classes + ' Sesi'" subtext="Menunggu Penguncian" color="orange" />
        <StatCard title="Belum Diisi" :value="stats.empty_classes + ' Sesi'" subtext="Perlu Follow-up" color="green" />
      </div>

      <!-- Master Override Table -->
      <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Status Sesi Presensi Rombongan Belajar (Tanggal: {{ formattedDate }})</h3>
            <p class="text-xs text-gray-400 mt-0.5">Superadmin berhak melakukan Force Unlock untuk membatalkan penguncian sesi PIN</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
            Hak Akses: Superadmin Override
          </span>
        </div>

        <div v-if="loading" class="py-16 text-center text-gray-400 text-xs">
          Memuat status sesi presensi seluruh kelas...
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                <th class="py-3 px-4 w-12 text-center">No</th>
                <th class="py-3 px-4">Rombel / Kelas</th>
                <th class="py-3 px-4">Wali Kelas</th>
                <th class="py-3 px-4">Sekretaris</th>
                <th class="py-3 px-4 text-center">Status Sesi</th>
                <th class="py-3 px-4 text-center">Kunci Oleh</th>
                <th class="py-3 px-4 text-right">Rasio Hadir</th>
                <th class="py-3 px-4 text-center">Tindakan Override</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(item, idx) in monitoringList" :key="item.id" class="hover:bg-gray-50/50 transition">
                <td class="py-3.5 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">{{ item.class_name }}</td>
                <td class="py-3.5 px-4 text-gray-700 font-medium">{{ item.advisor_name }}</td>
                <td class="py-3.5 px-4 text-gray-700">{{ item.secretary_name }}</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    v-if="item.session_status === 'LOCKED'"
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1"
                  >
                    <Lock class="w-3 h-3" />
                    <span>LOCKED (Terkunci)</span>
                  </span>
                  <span
                    v-else-if="item.session_status === 'DRAFT'"
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1"
                  >
                    <FileEdit class="w-3 h-3" />
                    <span>DRAFT (Terbuka)</span>
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-50 text-gray-400 border border-gray-200 inline-flex items-center gap-1"
                  >
                    <span>○ Belum Ada Sesi</span>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center font-medium text-gray-500">
                  <span v-if="item.locked_by">{{ item.locked_by }}</span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <span class="font-bold text-gray-900">{{ item.attendance_percent }}%</span>
                  <span class="block text-[10px] text-gray-400">{{ item.present_ratio }}</span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center gap-2">
                    <!-- FORCE UNLOCK BUTTON -->
                    <button
                      v-if="item.session_status === 'LOCKED'"
                      @click="triggerForceUnlock(item)"
                      :disabled="unlockingId === item.id"
                      class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer disabled:opacity-50"
                      title="Kembalikan status sesi ke DRAFT agar sekretaris dapat mengubah kehadiran"
                    >
                      <Unlock class="w-3.5 h-3.5" />
                      <span>{{ unlockingId === item.id ? 'Membuka...' : 'Force Unlock' }}</span>
                    </button>

                    <!-- DIRECT FORM BUTTON -->
                    <NuxtLink
                      :to="`/presensi?class_id=${item.id}&date=${selectedDate}`"
                      class="px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-semibold transition"
                      title="Lihat formulir presensi rombel ini"
                    >
                      Buka Form
                    </NuxtLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Lock,
  Unlock,
  FileEdit,
  RefreshCw,
  BarChart2,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'

const selectedDate = ref(new Date().toISOString().split('T')[0])
const loading = ref(false)
const unlockingId = ref<number | null>(null)
const successMsg = ref('')
const errorMsg = ref('')

const monitoringList = ref<any[]>([])

const stats = computed(() => {
  const total = monitoringList.value.length
  const locked = monitoringList.value.filter(m => m.session_status === 'LOCKED').length
  const draft = monitoringList.value.filter(m => m.session_status === 'DRAFT').length
  const empty = total - (locked + draft)
  return {
    total_classes: total,
    locked_classes: locked,
    draft_classes: draft,
    empty_classes: empty,
  }
})

const formattedDate = computed(() => {
  return selectedDate.value
})

function setToday() {
  selectedDate.value = new Date().toISOString().split('T')[0]
  fetchMonitoring()
}

async function fetchMonitoring() {
  loading.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res: any = await $fetch(`${apiBase}/dashboard`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res && res.classes_monitoring) {
      monitoringList.value = res.classes_monitoring
    }
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat monitoring sesi presensi.'
  } finally {
    loading.value = false
  }
}

async function triggerForceUnlock(item: any) {
  if (!confirm(`Buka paksa (Force Unlock) sesi presensi rombel ${item.class_name} tanggal ${selectedDate.value}? Sesi akan kembali menjadi DRAFT dan dapat diedit oleh sekretaris.`)) {
    return
  }

  unlockingId.value = item.id
  try {
    const res: any = await $fetch(`${apiBase}/attendance/force-unlock`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        class_id: item.id,
        date: selectedDate.value,
      }
    })
    successMsg.value = res.message || `Sesi presensi ${item.class_name} berhasil dibuka kembali ke status DRAFT.`
    item.session_status = 'DRAFT'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal membuka kunci sesi presensi.'
  } finally {
    unlockingId.value = null
  }
}

onMounted(() => {
  fetchMonitoring()
})
</script>
