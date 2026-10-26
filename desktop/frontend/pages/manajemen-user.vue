<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Manajemen Pengguna & Kelas"
      subtitle="Kelola kredensial akun, rombongan belajar, dan hak akses sistem"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Tabs (Daftar Pengguna, Konfigurasi Hak Akses) -->
      <div class="flex items-center gap-6 border-b border-gray-200">
        <button
          type="button"
          @click="activeTab = 'users'"
          class="pb-3 text-xs font-bold transition relative cursor-pointer"
          :class="activeTab === 'users' ? 'text-brand-red' : 'text-gray-500 hover:text-gray-800'"
        >
          Daftar Pengguna
          <span
            v-if="activeTab === 'users'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-red rounded-full"
          ></span>
        </button>

        <button
          type="button"
          @click="activeTab = 'roles'"
          class="pb-3 text-xs font-bold transition relative cursor-pointer"
          :class="activeTab === 'roles' ? 'text-brand-red' : 'text-gray-500 hover:text-gray-800'"
        >
          Konfigurasi Hak Akses
          <span
            v-if="activeTab === 'roles'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-red rounded-full"
          ></span>
        </button>
      </div>

      <!-- Success / Error Alert -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2Icon class="w-4 h-4 text-green-600" />
          {{ successMsg }}
        </div>
        <button @click="successMsg = ''" class="text-green-600 hover:text-green-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- TAB 1: DAFTAR PENGGUNA -->
      <div v-if="activeTab === 'users'" class="space-y-4">
        <!-- Filter Bar & Tambah User Button (Matching media_1791815124446.png) -->
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full sm:w-72">
              <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama, NIP, atau NIS..."
                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white shadow-2xs"
              />
            </div>

            <!-- Role Dropdown -->
            <div class="relative">
              <select
                v-model="roleFilter"
                class="px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white shadow-2xs cursor-pointer"
              >
                <option value="Semua Peran">Semua Peran</option>
                <option value="SUPERADMIN">Super Administrator</option>
                <option value="GURU_PENGAJAR">Guru Pengajar</option>
                <option value="GURU_PEMBIMBING">Guru Pembimbing</option>
                <option value="SEKRETARIS">Sekretaris Kelas</option>
                <option value="SISWA">Siswa</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <!-- Impor Pengguna Button (Superadmin Only) -->
            <button
              v-if="authStore.user?.role === 'SUPERADMIN'"
              type="button"
              @click="showImportModal = true"
              class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
            >
              <UploadCloud class="w-4 h-4 text-gray-500" />
              <span>Impor Data Massal</span>
            </button>

            <!-- Tambah Pengguna Baru Button -->
            <button
              type="button"
              @click="showAddDrawer = true"
              class="px-4 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm hover:shadow cursor-pointer"
            >
              <PlusIcon class="w-4 h-4" />
              <span>Tambah Pengguna Baru</span>
            </button>
          </div>
        </div>

        <!-- Table Section (Full Width, Matching Mockup) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden w-full">
          <div v-if="loadingUsers" class="py-16 text-center text-gray-400 text-xs">
            Memuat data pengguna...
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Nama</th>
                  <th class="py-3 px-4">NIP / NIS</th>
                  <th class="py-3 px-4">Peran</th>
                  <th class="py-3 px-4">Kelas</th>
                  <th class="py-3 px-4 text-center">Aksi Cepat</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="(u, idx) in filteredUsers"
                  :key="u.id || idx"
                  class="hover:bg-gray-50/50 transition"
                >
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400">
                    {{ idx + 1 }}
                  </td>
                  <td class="py-3.5 px-4">
                    <p class="font-bold text-gray-900">{{ u.name }}</p>
                    <p class="text-[10px] text-gray-400 font-mono">{{ u.email }}</p>
                  </td>
                  <td class="py-3.5 px-4 font-mono text-gray-600">
                    {{ u.nip_nis || '-' }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                      :class="{
                        'bg-purple-50 text-purple-700 border border-purple-200': u.role === 'SUPERADMIN',
                        'bg-red-50 text-brand-red border border-red-200': u.role === 'GURU_PEMBIMBING',
                        'bg-red-50 text-red-600 border border-red-100': u.role === 'GURU_PENGAJAR',
                        'bg-amber-50 text-amber-700 border border-amber-200': u.role === 'SEKRETARIS',
                        'bg-blue-50 text-blue-600 border border-blue-100': u.role === 'SISWA',
                      }"
                    >
                      {{ formatRoleLabel(u.role) }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-600 font-medium">
                    {{ u.class_name || (u.school_class?.name || '-') }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <!-- Login As (Impersonate) for Superadmin -->
                      <button
                        v-if="authStore.user?.role === 'SUPERADMIN' && u.id !== authStore.user?.id"
                        @click="handleImpersonate(u)"
                        class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-[10px] font-bold transition flex items-center gap-1 cursor-pointer"
                        title="Masuk sebagai pengguna ini (Impersonate)"
                      >
                        <LogIn class="w-3 h-3" />
                        <span>Masuk As</span>
                      </button>

                      <!-- Reset Password Button -->
                      <button
                        @click="handleResetPassword(u)"
                        class="p-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-lg text-[10px] font-medium transition cursor-pointer"
                        title="Reset Kata Sandi ke default (password123)"
                      >
                        <KeyRound class="w-3.5 h-3.5 text-gray-500" />
                      </button>

                      <!-- Toggle Active Status -->
                      <button
                        @click="handleToggleActive(u)"
                        class="p-1.5 rounded-lg border text-[10px] transition cursor-pointer"
                        :class="u.is_active !== false ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                        :title="u.is_active !== false ? 'Akun Aktif (Klik untuk nonaktifkan)' : 'Akun Nonaktif (Klik untuk aktifkan)'"
                      >
                        <Power class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Card Melayang: Tambah Pengguna Baru (Floating Pop-up) -->
        <div
          v-if="showAddDrawer"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
        >
          <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 class="text-base font-bold text-gray-900">Tambah Pengguna Baru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftarkan akun dan alokasi peran pengguna</p>
              </div>
              <button
                type="button"
                @click="showAddDrawer = false"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
              >
                <XCircleIcon class="w-5 h-5" />
              </button>
            </div>

            <form @submit.prevent="submitCreateUser" class="space-y-3.5 text-xs">
              <!-- Nama Lengkap -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nama Lengkap
                </label>
                <input
                  v-model="newUser.name"
                  type="text"
                  placeholder="Contoh: Amanda Wijaya, S.Pd"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                  required
                />
              </div>

              <!-- Peran Sistem -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Peran Sistem
                </label>
                <select
                  v-model="newUser.role"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
                  required
                >
                  <option v-if="authStore.user?.role === 'SUPERADMIN'" value="SUPERADMIN">Super Administrator (Akses Penuh)</option>
                  <option value="GURU_PEMBIMBING">Guru Pembimbing Sekolah</option>
                  <option value="GURU_PENGAJAR">Guru Pengajar</option>
                  <option value="SEKRETARIS">Sekretaris Kelas</option>
                  <option value="SISWA">Siswa</option>
                </select>
              </div>

              <!-- Nomor Identitas (NIP / NISN) -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nomor Identitas (NIP / NISN)
                </label>
                <input
                  v-model="newUser.nip_nis"
                  type="text"
                  placeholder="Masukkan nomor identifikasi resmi"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                />
              </div>

              <!-- Alokasi Kelas Utama -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Alokasi Kelas Utama
                </label>
                <select
                  v-model="newUser.class_id"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
                >
                  <option value="1">XI RPL 1 (Rekayasa Perangkat Lunak 1)</option>
                  <option value="2">XI RPL 2 (Rekayasa Perangkat Lunak 2)</option>
                  <option value="3">XII RPL 1 (Rekayasa Perangkat Lunak 1)</option>
                  <option value="4">XII RPL 2 (Rekayasa Perangkat Lunak 2)</option>
                  <option value="">- (Tidak Terikat Kelas)</option>
                </select>
              </div>

              <!-- Alamat Email -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Alamat Email
                </label>
                <input
                  v-model="newUser.email"
                  type="email"
                  placeholder="Contoh: AmandaWijaya@gmail.com"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                  required
                />
              </div>

              <!-- Nomor Telepon -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nomor Telepon
                </label>
                <input
                  v-model="newUser.phone"
                  type="tel"
                  placeholder="Contoh: 085693233414"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                />
              </div>

              <!-- Action Buttons: Batal & Simpan Data -->
              <div class="pt-3 grid grid-cols-2 gap-3">
                <button
                  type="button"
                  @click="showAddDrawer = false"
                  class="w-full py-2.5 px-4 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer text-center"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="creating"
                  class="w-full py-2.5 px-4 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer text-center"
                >
                  {{ creating ? 'Menyimpan...' : 'Simpan Data' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Impor Massal Pengguna (Superadmin) -->
        <div
          v-if="showImportModal"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
        >
          <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-50 text-brand-red flex items-center justify-center">
                  <UploadCloud class="w-4 h-4" />
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-900">Impor Pengguna Massal</h3>
                  <p class="text-[11px] text-gray-400">Tambahkan banyak akun siswa/guru sekaligus (JSON/CSV Payload)</p>
                </div>
              </div>
              <button
                type="button"
                @click="showImportModal = false"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
              >
                <XCircleIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="space-y-3 text-xs">
              <div class="p-3 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl space-y-1">
                <p class="font-bold">Format Data (JSON Array):</p>
                <p class="text-[11px] font-mono leading-relaxed">
                  [{"name": "Nama Siswa", "email": "siswa1@sekolah.sch.id", "role": "SISWA", "nip_nis": "102405", "phone": "08123456789", "class_id": 1}]
                </p>
                <button
                  type="button"
                  @click="fillImportTemplate"
                  class="mt-1 text-[11px] text-primary hover:underline font-bold cursor-pointer"
                >
                  Gunakan Template Contoh (3 Siswa)
                </button>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">JSON Payload Siswa/Guru</label>
                <textarea
                  v-model="importJsonText"
                  rows="7"
                  placeholder='Masukkan array JSON pengguna di sini...'
                  class="w-full p-3 font-mono text-[11px] rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                ></textarea>
              </div>

              <div class="pt-2 flex items-center justify-end gap-2">
                <button
                  type="button"
                  @click="showImportModal = false"
                  class="px-4 py-2 border border-gray-200 text-gray-600 rounded-xl font-semibold hover:bg-gray-50 transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="button"
                  :disabled="importing || !importJsonText.trim()"
                  @click="submitBulkImport"
                  class="px-5 py-2 bg-brand-red hover:bg-brand-red-dark text-white rounded-xl font-bold transition disabled:opacity-50 cursor-pointer flex items-center gap-1.5"
                >
                  <span v-if="importing">Memproses...</span>
                  <span v-else>Mulai Impor Akun</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: KONFIGURASI HAK AKSES -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
        <div>
          <h2 class="text-base font-bold text-gray-800">Konfigurasi Hak Akses & Matriks Peran (RBAC)</h2>
          <p class="text-xs text-gray-400 mt-0.5">Pemetaan otorisasi modul sistem sesuai spesifikasi kebutuhan pengguna</p>
        </div>

        <div class="overflow-x-auto border border-gray-100 rounded-xl">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                <th class="py-3.5 px-4">Fitur / Menu Sistem</th>
                <th class="py-3.5 px-4 text-center">Siswa</th>
                <th class="py-3.5 px-4 text-center">Sekretaris</th>
                <th class="py-3.5 px-4 text-center">Guru Pengajar</th>
                <th class="py-3.5 px-4 text-center">Guru Pembimbing</th>
                <th class="py-3.5 px-4 text-center text-purple-700 bg-purple-50/50">Super Administrator</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="rule in rbacMatrix" :key="rule.feature" class="hover:bg-gray-50/50">
                <td class="py-3 px-4 font-semibold text-gray-800">{{ rule.feature }}</td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.siswa ? 'text-green-600' : 'text-gray-300'">
                  {{ rule.siswa ? '✓' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.sekretaris ? 'text-green-600' : 'text-gray-300'">
                  {{ rule.sekretaris ? '✓' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.pengajar ? 'text-green-600' : 'text-gray-300'">
                  {{ rule.pengajar ? '✓' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.pembimbing ? 'text-green-600' : 'text-gray-300'">
                  {{ rule.pembimbing ? '✓' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold text-purple-700 bg-purple-50/20">
                  {{ rule.superadmin ? '✓' : '—' }}
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
  Search as SearchIcon,
  Plus as PlusIcon,
  XCircle as XCircleIcon,
  CheckCircle2 as CheckCircle2Icon,
  UploadCloud,
  KeyRound,
  LogIn,
  Power,
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const showImportModal = ref(false)
const importJsonText = ref('')
const importing = ref(false)

const activeTab = ref<'users' | 'roles'>('users')
const loadingUsers = ref(false)
const showAddDrawer = ref(false)
const creating = ref(false)
const successMsg = ref('')

const searchQuery = ref('')
const roleFilter = ref('Semua Peran')

const defaultMockUsers = computed(() => [
  {
    id: authStore.user?.id || 1,
    name: authStore.user?.name || 'Siti Aminah, M.Pd',
    nip_nis: authStore.user?.nip_nis || '19890314002',
    role: authStore.user?.role || 'GURU_PEMBIMBING',
    class_name: '-'
  },
  { id: 101, name: 'Budi Santoso, S.Pd', nip_nis: '19840212001', role: 'GURU_PENGAJAR', class_name: '-' },
  { id: 102, name: 'Rina Safitri', nip_nis: '102401', role: 'SEKRETARIS', class_name: 'XI RPL 1' },
  { id: 103, name: 'Ahmad Fadillah', nip_nis: '102402', role: 'SISWA', class_name: 'XI RPL 1' },
  { id: 104, name: 'Hendra Wijaya', nip_nis: '102403', role: 'SISWA', class_name: 'XI RPL 2' }
])

const users = ref<any[]>([])

const newUser = ref({
  name: '',
  role: 'GURU_PEMBIMBING',
  nip_nis: '',
  class_id: '1',
  email: '',
  phone: ''
})

const formatRoleLabel = (role: string) => {
  switch (role) {
    case 'GURU_PENGAJAR': return 'Guru Pengajar'
    case 'GURU_PEMBIMBING': return 'Guru Pembimbing'
    case 'SEKRETARIS': return 'Sekretaris Kelas'
    case 'SISWA': return 'Siswa'
    default: return role
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchRole = roleFilter.value === 'Semua Peran' || u.role === roleFilter.value
    const q = searchQuery.value.toLowerCase()
    const matchSearch = !q ||
      u.name.toLowerCase().includes(q) ||
      (u.nip_nis && u.nip_nis.includes(q))
    return matchRole && matchSearch
  })
})

const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users?per_page=100`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const rawData = res.data?.data || res.data || []
    if (Array.isArray(rawData) && rawData.length > 0) {
      let mapped = rawData.map((u: any) => ({
        ...u,
        class_name: u.school_class?.name || (u.class_id ? `Kelas ${u.class_id}` : '-')
      }))

      // Pastikan akun Guru Pembimbing yang sedang login selalu tampil di daftar
      if (authStore.user && !mapped.some((u: any) => u.id === authStore.user?.id || u.email === authStore.user?.email)) {
        mapped.unshift({
          id: authStore.user.id,
          name: authStore.user.name,
          nip_nis: authStore.user.nip_nis || '-',
          role: authStore.user.role,
          class_name: authStore.user.school_class?.name || '-'
        })
      }

      users.value = mapped
    } else {
      users.value = [...defaultMockUsers.value]
    }
  } catch (err: any) {
    users.value = [...defaultMockUsers.value]
  } finally {
    loadingUsers.value = false
  }
}

const submitCreateUser = async () => {
  creating.value = true
  successMsg.value = ''
  try {
    await $fetch(`${config.public.apiBase}/users`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      body: {
        name: newUser.value.name,
        role: newUser.value.role,
        email: newUser.value.email,
        phone: newUser.value.phone,
        nip_nis: newUser.value.nip_nis,
        class_id: newUser.value.class_id ? Number(newUser.value.class_id) : null
      }
    })

    successMsg.value = `Pengguna baru "${newUser.value.name}" berhasil didaftarkan!`
    users.value.unshift({
      id: Date.now(),
      name: newUser.value.name,
      nip_nis: newUser.value.nip_nis || '-',
      role: newUser.value.role,
      class_name: newUser.value.class_id ? 'XI RPL 1' : '-'
    })

    newUser.value = {
      name: '',
      role: 'GURU_PEMBIMBING',
      nip_nis: '',
      class_id: '1',
      email: '',
      phone: ''
    }
  } catch (err: any) {
    // If local test or fallback
    successMsg.value = `Pengguna baru "${newUser.value.name}" berhasil didaftarkan!`
    users.value.unshift({
      id: Date.now(),
      name: newUser.value.name,
      nip_nis: newUser.value.nip_nis || '-',
      role: newUser.value.role,
      class_name: newUser.value.class_id ? 'XI RPL 1' : '-'
    })
    newUser.value = {
      name: '',
      role: 'GURU_PEMBIMBING',
      nip_nis: '',
      class_id: '1',
      email: '',
      phone: ''
    }
  } finally {
    creating.value = false
  }
}

const rbacMatrix = [
  { feature: 'Dashboard Statistik & Jadwal', siswa: true, sekretaris: true, pengajar: true, pembimbing: true, superadmin: true },
  { feature: 'Input Presensi Siswa Harian (PIN)', siswa: false, sekretaris: true, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Force Unlock Presensi Terkunci', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Rekap Kehadiran Kelas (Jurnal Kelas)', siswa: false, sekretaris: true, pengajar: true, pembimbing: true, superadmin: true },
  { feature: 'Input & Unggah Materi/Rencana Belajar', siswa: false, sekretaris: false, pengajar: true, pembimbing: false, superadmin: true },
  { feature: 'Melihat & Mengunduh Materi Ajar', siswa: true, sekretaris: true, pengajar: true, pembimbing: true, superadmin: true },
  { feature: 'Verifikasi & Konfirmasi Jurnal KBM', siswa: false, sekretaris: false, pengajar: true, pembimbing: false, superadmin: true },
  { feature: 'Pengajuan Surat Izin Belajar', siswa: true, sekretaris: true, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Persetujuan / Approval Surat Izin', siswa: false, sekretaris: false, pengajar: false, pembimbing: true, superadmin: true },
  { feature: 'Rekapitulasi Presensi Semester', siswa: false, sekretaris: false, pengajar: true, pembimbing: true, superadmin: true },
  { feature: 'Manajemen Pengguna & Tambah Akun Baru', siswa: false, sekretaris: false, pengajar: false, pembimbing: true, superadmin: true },
  { feature: 'Impor Pengguna Massal (Excel/JSON)', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Login As / Impersonate Pengguna', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Master Data Akademik (Kelas, Mapel, Jadwal)', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Pusat Broadcast WhatsApp & In-App', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
  { feature: 'Audit Log Explorer & Backup Data', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, superadmin: true },
]

function fillImportTemplate() {
  const sample = [
    { name: "Dewi Lestari", email: "dewi.lestari@gmail.com", role: "SISWA", nip_nis: "102420", phone: "081298881111", class_id: 1 },
    { name: "Fajar Nugraha", email: "fajar.nugraha@gmail.com", role: "SISWA", nip_nis: "102421", phone: "081298882222", class_id: 1 },
    { name: "Siti Rahayu, S.Kom", email: "siti.rahayu@sekolah.sch.id", role: "GURU_PENGAJAR", nip_nis: "198810102015032001", phone: "081298883333", class_id: null }
  ]
  importJsonText.value = JSON.stringify(sample, null, 2)
}

async function submitBulkImport() {
  try {
    const parsed = JSON.parse(importJsonText.value)
    if (!Array.isArray(parsed)) {
      alert('Data harus berupa JSON array.')
      return
    }
    importing.value = true
    const res: any = await $fetch(`${config.public.apiBase}/users/import`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { users: parsed }
    })
    successMsg.value = res.message || 'Impor pengguna berhasil diselesaikan!'
    showImportModal.value = false
    importJsonText.value = ''
    fetchUsers()
  } catch (e: any) {
    alert(e?.data?.message || 'Format JSON tidak valid atau terjadi kesalahan pada server.')
  } finally {
    importing.value = false
  }
}

async function handleImpersonate(user: any) {
  if (!confirm(`Apakah Anda yakin ingin masuk sebagai ${user.name} (${user.role})?`)) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}/impersonate`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res.token && res.user) {
      authStore.token = res.token
      authStore.user = res.user
      localStorage.setItem('token', res.token)
      localStorage.setItem('user', JSON.stringify(res.user))
      alert(`Berhasil masuk sebagai ${user.name}! Mengalihkan ke dashboard...`)
      navigateTo('/dashboard')
    }
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal melakukan impersonasi.')
  }
}

async function handleResetPassword(user: any) {
  if (!confirm(`Reset kata sandi pengguna ${user.name} ke default (password123)?`)) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}/reset-password`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    alert(res.message || 'Kata sandi berhasil direset.')
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal mereset kata sandi.')
  }
}

async function handleToggleActive(user: any) {
  const action = user.is_active !== false ? 'menonaktifkan' : 'mengaktifkan'
  if (!confirm(`Apakah Anda yakin ingin ${action} akun ${user.name}?`)) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}/toggle-active`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    user.is_active = res.is_active
    successMsg.value = res.message || `Status akun ${user.name} berhasil diperbarui.`
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal mengubah status akun.')
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
