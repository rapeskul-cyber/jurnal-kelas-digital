<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Buku Agenda KBM Kelas"
      :subtitle="'Pencatatan realisasi kegiatan belajar mengajar (KBM) tatap muka per jam pelajaran • ' + currentClassName"
    >
      <template #actions>
        <button
          v-if="canCreateAgenda"
          @click="openAddModal"
          type="button"
          class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white transition-colors text-xs font-bold flex items-center gap-2 shadow-xs cursor-pointer whitespace-nowrap active:scale-[0.99]"
        >
          <Plus class="w-4 h-4" />
          <span>Isi Agenda KBM</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- Success / Info Toast -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Filter Bar: Tanggal, Kelas Dinamis, Status, & Search -->
      <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Filter Tanggal -->
          <div class="flex items-center gap-2 px-3.5 py-2 bg-white rounded-xl border border-gray-200 hover:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary text-xs font-semibold text-gray-700 shadow-2xs transition">
            <Calendar class="w-4 h-4 text-primary shrink-0" />
            <input
              v-model="filterDate"
              type="date"
              @change="fetchAgendaJournals"
              class="border-none bg-transparent text-xs font-semibold text-gray-800 focus:outline-none cursor-pointer"
            />
          </div>

          <!-- Filter Kelas: Terkunci untuk Siswa / Sekretaris, Pilihan Dinamis dari Database untuk Guru/Admin -->
          <div class="relative">
            <div
              v-if="isClassLocked"
              class="px-3.5 py-2 rounded-xl bg-blue-50/60 border border-blue-200 text-xs font-semibold text-primary shadow-2xs flex items-center gap-1.5 whitespace-nowrap"
            >
              <span class="w-2 h-2 rounded-full bg-primary"></span>
              <span>Kelas: {{ currentClassName }}</span>
            </div>
            <select
              v-else
              v-model="selectedClassId"
              @change="onClassChange"
              class="px-3 py-2 pr-8 rounded-xl bg-white border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-2xs cursor-pointer whitespace-nowrap"
            >
              <option v-for="cls in availableClasses" :key="cls.id" :value="String(cls.id)">
                Kelas: {{ cls.name }}
              </option>
            </select>
          </div>

          <!-- Filter Status Verifikasi Guru -->
          <div class="relative">
            <select
              v-model="filterStatus"
              class="px-3 py-2 pr-8 rounded-xl bg-white border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-2xs cursor-pointer whitespace-nowrap"
            >
              <option value="ALL">Semua Status Konfirmasi</option>
              <option value="TERVERIFIKASI">Terverifikasi Guru</option>
              <option value="PENDING">Menunggu Konfirmasi</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Search Box -->
          <div class="relative w-full sm:w-64">
            <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari mapel, guru, materi..."
              class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-2xs"
            />
          </div>

          <!-- Ekspor CSV / Print -->
          <button
            @click="exportCsv"
            type="button"
            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-gray-200 hover:border-primary text-gray-700 hover:text-primary text-xs font-semibold shadow-2xs transition cursor-pointer whitespace-nowrap"
          >
            <Download class="w-3.5 h-3.5" />
            <span>Ekspor Agenda</span>
          </button>
        </div>
      </div>

      <!-- 4 Stat Summary Cards (Buku Agenda Kelas) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shrink-0 border border-blue-100">
            <BookOpen class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Total Sesi KBM</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ agendaList.length }}</span>
              <span class="text-[11px] font-normal text-gray-400">pertemuan</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Terverifikasi Guru</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ verifiedCount }}</span>
              <span class="text-[11px] font-normal text-emerald-600">terkonfirmasi</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
            <Clock class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Menunggu Konfirmasi</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ pendingCount }}</span>
              <span class="text-[11px] font-normal text-amber-600">sesi</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
            <FileSpreadsheet class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Jadwal Hari Ini</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ schedules.length }}</span>
              <span class="text-[11px] font-normal text-gray-400">jam mapel</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Agenda Table -->
      <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Riwayat Agenda Realisasi Pembelajaran (KBM)</h3>
            <p class="text-xs text-gray-400 mt-0.5">Catatan pokok bahasan, jam tatap muka, dan konfirmasi paraf kehadiran guru pengajar di kelas</p>
          </div>
          <span class="text-xs text-gray-500 font-medium">
            Tanggal: <strong>{{ formattedFilterDate }}</strong>
          </span>
        </div>

        <div v-if="loading" class="py-16 text-center text-gray-400 text-xs">
          Memuat data buku agenda KBM kelas...
        </div>

        <div v-else-if="filteredAgenda.length === 0" class="py-16 text-center space-y-3">
          <BookOpen class="w-10 h-10 text-gray-300 mx-auto" />
          <div>
            <p class="text-sm font-semibold text-gray-700">Belum Ada Agenda KBM Dicatat</p>
            <p class="text-xs text-gray-400 mt-0.5">Belum ada catatan realisasi jam pelajaran kelas pada tanggal ini.</p>
          </div>
          <button
            v-if="canCreateAgenda"
            type="button"
            @click="openAddModal"
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Isi Agenda KBM Sekarang</span>
          </button>
        </div>

        <div v-else>
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3.5">
            <div
              v-for="(agenda, idx) in filteredAgenda"
              :key="'mob-' + agenda.id"
              class="p-4 rounded-2xl border border-gray-200 bg-white shadow-2xs space-y-3"
            >
              <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-blue-50 text-primary font-bold text-xs flex items-center justify-center shrink-0">
                    {{ String(idx + 1).padStart(2, '0') }}
                  </span>
                  <div class="font-bold text-gray-900 text-xs">{{ agenda.time_slot }}</div>
                  <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md font-medium">
                    {{ agenda.room || 'Kelas' }}
                  </span>
                </div>

                <span
                  v-if="agenda.status === 'TERVERIFIKASI'"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0"
                >
                  <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                  <span>Terverifikasi</span>
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0"
                >
                  <Clock class="w-3 h-3 text-amber-600" />
                  <span>Menunggu</span>
                </span>
              </div>

              <div>
                <h4 class="font-bold text-primary text-sm">{{ agenda.subject }}</h4>
                <p class="text-xs text-gray-600 mt-0.5 flex items-center gap-1">
                  <User class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                  <span>{{ agenda.teacher }}</span>
                </p>
              </div>

              <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100 space-y-1 text-xs">
                <p class="font-semibold text-gray-800"><span class="text-gray-400 font-normal">Pokok Bahasan:</span> {{ agenda.topic }}</p>
                <p class="text-gray-600 leading-relaxed"><span class="text-gray-400 font-normal">Aktivitas:</span> {{ agenda.activity }}</p>
                <p v-if="agenda.notes" class="text-gray-500 italic text-[11px] pt-1 border-t border-gray-200/50">Catatan: {{ agenda.notes }}</p>
              </div>

              <!-- Action buttons on mobile -->
              <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                <button
                  v-if="canConfirmAgenda && agenda.status === 'PENDING'"
                  type="button"
                  @click="confirmAgenda(agenda)"
                  class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs active:scale-95 min-h-[44px]"
                >
                  <Check class="w-4 h-4" />
                  <span>Sahkan Jam Ini</span>
                </button>

                <button
                  v-if="canManageAgenda"
                  type="button"
                  @click="openEditModal(agenda)"
                  class="py-2 px-3.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 text-xs font-semibold transition flex items-center justify-center gap-1 min-h-[44px]"
                >
                  <Pencil class="w-3.5 h-3.5" />
                  <span>Edit</span>
                </button>

                <button
                  v-if="canManageAgenda"
                  type="button"
                  @click="deleteAgenda(agenda)"
                  class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-semibold transition flex items-center justify-center min-h-[44px] min-w-[44px]"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase text-[10px] tracking-wider font-semibold">
                <th class="py-3 px-4 w-12 whitespace-nowrap">No</th>
                <th class="py-3 px-4 whitespace-nowrap">Jam / Waktu & Ruang</th>
                <th class="py-3 px-4 whitespace-nowrap">Mata Pelajaran & Guru</th>
                <th class="py-3 px-4 whitespace-nowrap">Pokok Bahasan / KD</th>
                <th class="py-3 px-4">Realisasi Aktivitas KBM</th>
                <th class="py-3 px-4 whitespace-nowrap">Catatan Kelas</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Status Konfirmasi</th>
                <th class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(agenda, idx) in filteredAgenda"
                :key="agenda.id"
                class="hover:bg-gray-50/50 transition"
              >
                <td class="py-3.5 px-4 font-medium text-gray-400 whitespace-nowrap">
                  {{ String(idx + 1).padStart(2, '0') }}
                </td>

                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-bold text-gray-900">{{ agenda.time_slot }}</div>
                  <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>{{ agenda.room || 'Kelas' }}</span>
                  </div>
                </td>

                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-bold text-primary">{{ agenda.subject }}</div>
                  <div class="text-[11px] text-gray-600 mt-0.5 flex items-center gap-1">
                    <User class="w-3 h-3 text-gray-400 shrink-0" />
                    <span>{{ agenda.teacher }}</span>
                  </div>
                </td>

                <td class="py-3.5 px-4 font-semibold text-gray-800 whitespace-nowrap">
                  {{ agenda.topic }}
                </td>

                <td class="py-3.5 px-4 text-gray-600 max-w-sm">
                  <div class="line-clamp-2 leading-relaxed" :title="agenda.activity">
                    {{ agenda.activity }}
                  </div>
                </td>

                <td class="py-3.5 px-4 text-gray-500 max-w-xs whitespace-nowrap">
                  <span class="truncate block" :title="agenda.notes || '-'">{{ agenda.notes || '-' }}</span>
                </td>

                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <span
                    v-if="agenda.status === 'TERVERIFIKASI'"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                    :title="agenda.confirmed_by ? ('Diverifikasi oleh ' + agenda.confirmed_by + ' pada ' + (agenda.confirmed_at || '')) : 'Terverifikasi'"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Terverifikasi Guru</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                    title="Menunggu konfirmasi / paraf guru pengajar terkait"
                  >
                    <Clock class="w-3.5 h-3.5 text-amber-600" />
                    <span>Menunggu Konfirmasi</span>
                  </span>
                </td>

                <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                  <!-- Tombol Konfirmasi Guru / Walas jika status PENDING -->
                  <button
                    v-if="canConfirmAgenda && agenda.status === 'PENDING'"
                    type="button"
                    @click="confirmAgenda(agenda)"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition shadow-2xs cursor-pointer active:scale-95"
                    title="Beri paraf dan konfirmasi realisasi jam pelajaran ini"
                  >
                    <Check class="w-3 h-3" />
                    <span>Sahkan</span>
                  </button>

                  <!-- Tombol Hapus (jika pembuat dan masih PENDING) -->
                  <button
                    v-if="agenda.created_by_id === authStore.user?.id && agenda.status === 'PENDING'"
                    type="button"
                    @click="deleteAgenda(agenda)"
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-rose-600 hover:bg-rose-50 border border-rose-200 text-[11px] font-bold transition cursor-pointer"
                    title="Hapus draf agenda KBM"
                  >
                    <Trash2 class="w-3 h-3" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      </div>

      <!-- Modal Input Agenda KBM Kelas -->
      <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
      >
        <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
          <!-- Mobile drag handle -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <h3 class="text-base font-bold text-gray-900">Pengisian Buku Agenda KBM Kelas</h3>
              <p class="text-xs text-gray-400 mt-0.5">Input catatan realisasi pertemuan mata pelajaran dan materi pembelajaran</p>
            </div>
            <button
              type="button"
              @click="showAddModal = false"
              class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitAgenda" class="space-y-4">
            <!-- Pilih Jadwal Pelajaran -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Jadwal / Jam Pelajaran & Guru
              </label>
              <select
                v-model="agendaForm.schedule_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white"
                required
              >
                <option value="" disabled>-- Pilih Jadwal Jam Pelajaran --</option>
                <option v-for="sch in schedules" :key="sch.id" :value="sch.id">
                  {{ sch.start_time }} - {{ sch.end_time }} | {{ sch.subject?.name }} ({{ sch.teacher?.name }}) - {{ sch.room || 'Lab. 1' }}
                </option>
              </select>
            </div>

            <!-- Tanggal Pembelajaran -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Tanggal Pembelajaran
              </label>
              <input
                v-model="agendaForm.date"
                type="date"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white"
                required
              />
            </div>

            <!-- Pokok Bahasan / KD -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Pokok Bahasan / KD / Materi
              </label>
              <input
                v-model="agendaForm.topic"
                type="text"
                placeholder="Contoh: Implementasi State Management Pinia & Nuxt 3"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                required
              />
            </div>

            <!-- Rangkuman Aktivitas & Realisasi KBM -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Rangkuman Aktivitas & Realisasi KBM di Kelas
              </label>
              <textarea
                v-model="agendaForm.activity"
                rows="3"
                placeholder="Rangkuman jalannya pembelajaran, penyampaian teori, praktikum laboratorium, dan tugas yang dikerjakan siswa..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                required
              ></textarea>
            </div>

            <!-- Catatan Kelas / Ruang Praktikum -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Catatan dari Sekretaris / Kondisi Kelas
              </label>
              <input
                v-model="agendaForm.notes"
                type="text"
                placeholder="Contoh: Pembelajaran berjalan tertib di Lab Komputer 1, seluruh PC berfungsi"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-gray-100">
              <button
                type="button"
                @click="showAddModal = false"
                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer whitespace-nowrap"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold shadow-xs transition disabled:opacity-50 cursor-pointer whitespace-nowrap"
              >
                {{ isSubmitting ? 'Menyimpan...' : 'Kirim Agenda KBM' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Calendar,
  Search,
  BookOpen,
  CheckCircle2,
  Clock,
  Plus,
  X,
  User,
  Download,
  FileSpreadsheet,
  Check,
  Trash2,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useToastStore } from '~/stores/toast'

const authStore = useAuthStore()
const toast = useToastStore()

const filterDate = ref(getLocalTodayYMD())
const selectedClassId = ref(authStore.user?.class_id ? String(authStore.user.class_id) : '1')
const filterStatus = ref('ALL')
const searchQuery = ref('')
const loading = ref(false)
const isSubmitting = ref(false)
const successMsg = ref('')
const showAddModal = ref(false)

const availableClasses = ref<any[]>([])
const schedules = ref<any[]>([])
const agendaList = ref<any[]>([])

const agendaForm = reactive({
  schedule_id: '' as string | number,
  date: getLocalTodayYMD(),
  topic: '',
  activity: '',
  notes: ''
})

const isClassLocked = computed(() => {
  return ['SEKRETARIS', 'SISWA'].includes(authStore.user?.role || '')
})

const currentClassName = computed(() => {
  if (isClassLocked.value) {
    return authStore.user?.school_class?.name || authStore.user?.class_name || 'Kelas Industri RPL'
  }
  const found = availableClasses.value.find(c => String(c.id) === String(selectedClassId.value))
  return found ? found.name : (authStore.user?.school_class?.name || 'Kelas Industri RPL')
})

const canCreateAgenda = computed(() => {
  return ['SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'].includes(authStore.user?.role || '')
})

const canConfirmAgenda = computed(() => {
  return ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'].includes(authStore.user?.role || '')
})

const formattedFilterDate = computed(() => {
  if (!filterDate.value) return 'Hari Ini'
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(new Date(filterDate.value))
})

const verifiedCount = computed(() => {
  return agendaList.value.filter(a => a.status === 'TERVERIFIKASI').length
})

const pendingCount = computed(() => {
  return agendaList.value.filter(a => a.status !== 'TERVERIFIKASI').length
})

const filteredAgenda = computed(() => {
  let list = agendaList.value

  if (filterStatus.value !== 'ALL') {
    list = list.filter(a => a.status === filterStatus.value)
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(a =>
      (a.subject && a.subject.toLowerCase().includes(q)) ||
      (a.teacher && a.teacher.toLowerCase().includes(q)) ||
      (a.topic && a.topic.toLowerCase().includes(q)) ||
      (a.activity && a.activity.toLowerCase().includes(q)) ||
      (a.notes && a.notes.toLowerCase().includes(q))
    )
  }

  return list
})

function getLocalTodayYMD(): string {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const fetchClasses = async () => {
  try {
    const res: any = await $fetch(`${authStore.apiBase}/classes`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    availableClasses.value = res || []
  } catch (e) {
    console.warn('Gagal memuat daftar kelas:', e)
  }
}

const fetchSchedules = async () => {
  try {
    const res: any = await $fetch(`${authStore.apiBase}/schedules`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      params: {
        class_id: selectedClassId.value
      }
    })
    schedules.value = res || []
    if (schedules.value.length > 0 && !agendaForm.schedule_id) {
      agendaForm.schedule_id = schedules.value[0].id
    }
  } catch (e) {
    console.warn('Gagal memuat jadwal pelajaran:', e)
  }
}

const fetchAgendaJournals = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/journals/class`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      params: {
        type: 'class_agenda',
        class_id: selectedClassId.value,
        date: filterDate.value
      }
    })
    agendaList.value = res?.data || []
  } catch (e) {
    console.warn('Gagal memuat agenda KBM kelas:', e)
    agendaList.value = []
  } finally {
    loading.value = false
  }
}

const onClassChange = () => {
  fetchSchedules()
  fetchAgendaJournals()
}

const openAddModal = () => {
  agendaForm.date = filterDate.value || getLocalTodayYMD()
  if (schedules.value.length > 0 && !agendaForm.schedule_id) {
    agendaForm.schedule_id = schedules.value[0].id
  }
  showAddModal.value = true
}

const submitAgenda = async () => {
  if (!agendaForm.schedule_id || !agendaForm.topic || !agendaForm.activity) {
    toast.warning('Harap lengkapi semua kolom wajib pada agenda KBM.')
    return
  }
  isSubmitting.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/journals/class`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        type: 'class_agenda',
        schedule_id: agendaForm.schedule_id,
        class_id: selectedClassId.value,
        date: agendaForm.date,
        topic: agendaForm.topic,
        activity: agendaForm.activity,
        notes: agendaForm.notes
      }
    })
    successMsg.value = res?.message || 'Agenda KBM kelas berhasil dicatat!'
    showAddModal.value = false
    agendaForm.topic = ''
    agendaForm.activity = ''
    agendaForm.notes = ''
    await fetchAgendaJournals()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal menyimpan agenda KBM.')
  } finally {
    isSubmitting.value = false
  }
}

const confirmAgenda = async (agenda: any) => {
  try {
    const res: any = await $fetch(`${authStore.apiBase}/journals/class/${agenda.id}/confirm`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {}
    })
    successMsg.value = res?.message || 'Agenda KBM berhasil disahkan & diverifikasi!'
    await fetchAgendaJournals()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal mengonfirmasi agenda KBM.')
  }
}

const deleteAgenda = async (agenda: any) => {
  if (!confirm(`Hapus draf agenda pembelajaran "${agenda.topic}"?`)) return
  try {
    await $fetch(`${authStore.apiBase}/journals/class/${agenda.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    successMsg.value = 'Agenda KBM berhasil dihapus.'
    await fetchAgendaJournals()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal menghapus agenda.')
  }
}

const exportCsv = () => {
  if (agendaList.value.length === 0) {
    toast.warning('Belum ada agenda KBM pada tanggal ini untuk diekspor.')
    return
  }
  const headers = ['No', 'Jam / Waktu', 'Ruang', 'Mata Pelajaran', 'Guru Pengajar', 'Pokok Bahasan', 'Aktivitas KBM', 'Catatan', 'Status Verifikasi']
  const rows = filteredAgenda.value.map((a, idx) => [
    idx + 1,
    `"${a.time_slot || '-'}"`,
    `"${a.room || '-'}"`,
    `"${a.subject || '-'}"`,
    `"${a.teacher || '-'}"`,
    `"${a.topic || '-'}"`,
    `"${(a.activity || '').replace(/"/g, '""')}"`,
    `"${(a.notes || '-').replace(/"/g, '""')}"`,
    `"${a.status}"`
  ])
  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF'
    + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Agenda_KBM_Kelas_${filterDate.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

onMounted(async () => {
  await fetchClasses()
  await fetchSchedules()
  await fetchAgendaJournals()
})
</script>
