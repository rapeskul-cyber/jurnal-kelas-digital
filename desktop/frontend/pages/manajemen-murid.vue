<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="pageTitle"
      subtitle="Kelola data siswa, angkat sekretaris kelas, dan kelola kata sandi murid di kelas binaan Anda"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- 3 Stat Cards Kelas -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Murid Terdaftar</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ students.length }} Siswa</h3>
            <p class="text-[11px] text-gray-500 font-medium mt-0.5">Kelas {{ className }}</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
            <GraduationCap class="w-6 h-6" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sekretaris Kelas</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ secretaryName }}</h3>
            <p class="text-[11px] text-purple-600 font-medium mt-0.5">Penanggung Jawab Presensi & Jurnal</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
            <UserCheck class="w-6 h-6" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa Reguler</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ regularStudentCount }} Siswa</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Akun Aktif Presensi</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <Users class="w-6 h-6" />
          </div>
        </div>
      </div>

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

      <!-- Action & Search Bar -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search -->
          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cari nama atau NISN murid..."
              class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-2xs"
            />
          </div>

          <!-- Role Filter -->
          <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-semibold">
            <button
              type="button"
              @click="setRoleFilter('ALL')"
              class="px-3 py-1.5 rounded-lg transition"
              :class="selectedRoleFilter === 'ALL' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'"
            >
              Semua Murid
            </button>
            <button
              type="button"
              @click="setRoleFilter('SEKRETARIS')"
              class="px-3 py-1.5 rounded-lg transition"
              :class="selectedRoleFilter === 'SEKRETARIS' ? 'bg-purple-600 text-white font-bold shadow-2xs' : 'text-gray-500 hover:text-purple-700'"
            >
              Sekretaris Kelas
            </button>
            <button
              type="button"
              @click="setRoleFilter('SISWA')"
              class="px-3 py-1.5 rounded-lg transition"
              :class="selectedRoleFilter === 'SISWA' ? 'bg-blue-600 text-white font-bold shadow-2xs' : 'text-gray-500 hover:text-blue-700'"
            >
              Siswa Reguler
            </button>
          </div>
        </div>

        <div>
          <!-- Tambah Murid Button -->
          <button
            type="button"
            @click="openAddModal"
            class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm hover:shadow cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>+ Tambah Murid Kelas</span>
          </button>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden w-full">
        <div v-if="loading" class="py-16 text-center text-gray-400 text-xs">
          Memuat data murid kelas...
        </div>

        <div v-else-if="!filteredStudents.length" class="py-16 text-center text-gray-400 text-xs">
          Belum ada murid yang sesuai dengan filter pencarian.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                <th class="py-3 px-4 w-12 text-center">No</th>
                <th class="py-3 px-4">Nama Siswa & NISN</th>
                <th class="py-3 px-4">Kontak (Email / No WA)</th>
                <th class="py-3 px-4 text-center">Peran Kelas</th>
                <th class="py-3 px-4 text-center">Status Akun</th>
                <th class="py-3 px-4 text-center">Aksi Cepat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(s, idx) in filteredStudents" :key="s.id" class="hover:bg-gray-50/60 transition">
                <td class="py-3.5 px-4 text-center text-gray-400 font-medium">{{ idx + 1 }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>{{ s.name }}</span>
                    <span
                      v-if="s.role === 'SEKRETARIS'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200"
                    >
                      Sekretaris
                    </span>
                  </div>
                  <div class="text-[11px] text-gray-500 font-mono mt-0.5">NISN: {{ s.nip_nis || '-' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="text-gray-800">{{ s.email }}</div>
                  <div class="text-[11px] text-gray-500 mt-0.5">{{ s.phone ? `WA: ${s.phone}` : 'No HP: -' }}</div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <!-- Toggle Role: Siswa vs Sekretaris -->
                  <button
                    type="button"
                    @click="handleToggleRole(s)"
                    class="px-3 py-1 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 mx-auto cursor-pointer"
                    :class="s.role === 'SEKRETARIS' ? 'bg-purple-100 hover:bg-purple-200 text-purple-800' : 'bg-gray-100 hover:bg-purple-50 hover:text-purple-700 text-gray-600'"
                    :title="s.role === 'SEKRETARIS' ? 'Klik untuk ubah menjadi Siswa Biasa' : 'Klik untuk jadikan Sekretaris Kelas'"
                  >
                    <UserCheck class="w-3.5 h-3.5" />
                    <span>{{ s.role === 'SEKRETARIS' ? 'Sekretaris' : 'Jadikan Sekretaris' }}</span>
                  </button>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    type="button"
                    @click="handleToggleActive(s)"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer"
                    :class="s.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200'"
                  >
                    {{ s.is_active ? '● Aktif' : '○ Nonaktif' }}
                  </button>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Edit -->
                    <button
                      type="button"
                      @click="openEditModal(s)"
                      class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-gray-800 transition cursor-pointer"
                      title="Edit Data Siswa"
                    >
                      <Edit3 class="w-4 h-4" />
                    </button>

                    <!-- Reset Password -->
                    <button
                      type="button"
                      @click="handleResetPassword(s)"
                      class="p-1.5 hover:bg-amber-50 rounded-lg text-amber-600 transition cursor-pointer"
                      title="Reset Kata Sandi ke password123"
                    >
                      <KeyRound class="w-4 h-4" />
                    </button>

                    <!-- Delete -->
                    <button
                      type="button"
                      @click="handleDelete(s)"
                      class="p-1.5 hover:bg-rose-50 rounded-lg text-rose-600 transition cursor-pointer"
                      title="Hapus Siswa dari Kelas"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- MODAL TAMBAH / EDIT MURID -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden border border-gray-150 animate-in fade-in duration-150">
        <div class="p-5 border-b border-gray-150 flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-gray-900">
              {{ isEditing ? 'Edit Data Murid' : 'Tambah Murid Kelas Baru' }}
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Kelas: {{ className }}</p>
          </div>
          <button @click="closeModal" class="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-5 space-y-4 text-xs">
          <!-- Peran Siswa -->
          <div>
            <label class="block font-bold text-gray-700 mb-1.5">Peran Siswa di Kelas *</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="form.role = 'SISWA'"
                class="p-3 rounded-xl border text-left transition cursor-pointer"
                :class="form.role === 'SISWA' ? 'border-blue-500 bg-blue-50 text-blue-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <div class="font-bold">Siswa Biasa</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Murid kelas reguler</div>
              </button>

              <button
                type="button"
                @click="form.role = 'SEKRETARIS'"
                class="p-3 rounded-xl border text-left transition cursor-pointer"
                :class="form.role === 'SEKRETARIS' ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <div class="font-bold">Sekretaris Kelas</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Petugas isi presensi & jurnal</div>
              </button>
            </div>
          </div>

          <!-- Nama Siswa -->
          <div>
            <label class="block font-bold text-gray-700 mb-1">Nama Lengkap Siswa *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Ahmad Fadillah"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <!-- NISN & No WA -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-gray-700 mb-1">NISN / NIS *</label>
              <input
                v-model="form.nip_nis"
                type="text"
                required
                placeholder="102402"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-gray-700 mb-1">No. WhatsApp Wali</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="628123456780"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block font-bold text-gray-700 mb-1">Email Siswa *</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="nama.siswa@gmail.com"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div class="pt-3 border-t border-gray-150 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <span>{{ saving ? 'Menyimpan...' : (isEditing ? 'Perbarui Murid' : 'Simpan Murid') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  GraduationCap,
  Users,
  UserCheck,
  Search,
  Plus,
  Edit3,
  KeyRound,
  Trash2,
  CheckCircle2,
  AlertCircle,
  X,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const students = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)

const searchQuery = ref('')
const selectedRoleFilter = ref('ALL')

const successMsg = ref('')
const errorMsg = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  name: '',
  email: '',
  nip_nis: '',
  phone: '',
  role: 'SISWA',
})

const className = computed(() => {
  return authStore.user?.guided_class?.name || authStore.user?.school_class?.name || 'XI RPL 1'
})

const pageTitle = computed(() => {
  return `Manajemen Murid Kelas ${className.value}`
})

const secretary = computed(() => students.value.find(s => s.role === 'SEKRETARIS'))
const secretaryName = computed(() => secretary.value?.name || 'Belum Ditentukan')
const regularStudentCount = computed(() => students.value.filter(s => s.role === 'SISWA').length)

const filteredStudents = computed(() => {
  return students.value.filter(s => {
    if (selectedRoleFilter.value !== 'ALL' && s.role !== selectedRoleFilter.value) {
      return false
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const matchName = s.name?.toLowerCase().includes(q)
      const matchNis = s.nip_nis?.toLowerCase().includes(q)
      const matchEmail = s.email?.toLowerCase().includes(q)
      return matchName || matchNis || matchEmail
    }
    return true
  })
})

async function fetchClassStudents() {
  if (!authStore.token) return
  loading.value = true
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    students.value = res.data || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat data murid kelas.'
  } finally {
    loading.value = false
  }
}

let searchTimer: any = null
function handleSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    // client-side reactive filter
  }, 200)
}

function setRoleFilter(role: string) {
  selectedRoleFilter.value = role
}

function openAddModal() {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    email: '',
    nip_nis: '',
    phone: '',
    role: 'SISWA',
  }
  showModal.value = true
}

function openEditModal(student: any) {
  isEditing.value = true
  editingId.value = student.id
  form.value = {
    name: student.name,
    email: student.email,
    nip_nis: student.nip_nis || '',
    phone: student.phone || '',
    role: student.role,
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function handleSubmit() {
  saving.value = true
  errorMsg.value = ''
  try {
    const url = isEditing.value
      ? `${authStore.apiBase}/users/${editingId.value}`
      : `${authStore.apiBase}/users`
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $fetch<any>(url, {
      method,
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: form.value,
    })

    successMsg.value = res.message || 'Data murid berhasil disimpan.'
    closeModal()
    fetchClassStudents()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan data murid.'
  } finally {
    saving.value = false
  }
}

async function handleToggleRole(student: any) {
  const newRole = student.role === 'SEKRETARIS' ? 'SISWA' : 'SEKRETARIS'
  const actionText = newRole === 'SEKRETARIS'
    ? `Angkat "${student.name}" sebagai Sekretaris Kelas ${className.value}?`
    : `Ubah status "${student.name}" menjadi Siswa Biasa?`

  if (!confirm(actionText)) return

  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${student.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { role: newRole },
    })

    successMsg.value = `Peran ${student.name} berhasil diubah menjadi ${newRole === 'SEKRETARIS' ? 'Sekretaris Kelas' : 'Siswa Biasa'}.`
    fetchClassStudents()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengubah peran siswa.'
  }
}

async function handleToggleActive(student: any) {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${student.id}/toggle-active`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    student.is_active = res.is_active
    successMsg.value = `Status akun ${student.name} berhasil diubah.`
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memperbarui status akun.'
  }
}

async function handleResetPassword(student: any) {
  if (!confirm(`Reset kata sandi murid "${student.name}" menjadi "password123"?`)) return
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${student.id}/reset-password`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    successMsg.value = res.message || `Kata sandi ${student.name} berhasil direset ke password123.`
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mereset kata sandi.'
  }
}

async function handleDelete(student: any) {
  if (!confirm(`Hapus murid "${student.name}" dari kelas ini?`)) return
  try {
    await $fetch(`${authStore.apiBase}/users/${student.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    successMsg.value = `Murid ${student.name} berhasil dihapus dari kelas.`
    fetchClassStudents()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menghapus data murid.'
  }
}

onMounted(() => {
  fetchClassStudents()
})
</script>
