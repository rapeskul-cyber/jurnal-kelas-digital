<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Master Data Akademik"
      subtitle="Kelola kelas, mata pelajaran, jadwal mengajar, dan tahun ajaran sekolah"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- Tabs Navigation -->
      <div class="flex items-center gap-6 border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="activeTab = tab.id"
          class="pb-3 text-xs font-bold transition relative cursor-pointer flex items-center gap-2"
          :class="activeTab === tab.id ? 'text-primary' : 'text-gray-500 hover:text-gray-800'"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          <span>{{ tab.label }}</span>
          <span
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"
          ></span>
        </button>
      </div>

      <!-- Alerts -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <!-- ============================================== -->
      <!-- TAB 1: KELAS               -->
      <!-- ============================================== -->
      <div v-if="activeTab === 'classes'" class="space-y-4">
        <!-- Top Row: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Daftar Kelas</h2>
            <p class="text-xs text-gray-400 mt-0.5">Kelola kelas, penugasan wali kelas, dan sekretaris</p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <button
              @click="showPromoteModal = true"
              class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer whitespace-nowrap"
            >
              <ArrowRightLeft class="w-4 h-4 text-gray-500" />
              <span>Mutasi Siswa</span>
            </button>
            <button
              @click="openAddClassModal"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap"
            >
              <Plus class="w-4 h-4" />
              <span>Tambah Kelas Baru</span>
            </button>
          </div>
        </div>

        <!-- Dedicated Header Bar: Program Keahlian & Stats -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-gray-150 shadow-2xs">
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1">Program Keahlian:</span>
            <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl text-xs select-none">
              <button
                type="button"
                @click="classMajorFilter = 'ALL'"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="classMajorFilter === 'ALL' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                Semua
              </button>
              <button
                v-for="m in uniqueMajors"
                :key="m"
                type="button"
                @click="classMajorFilter = m"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="classMajorFilter === m ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                {{ m }}
              </button>
            </div>
          </div>

          <div class="text-xs text-gray-500 font-medium px-2">
            Total <span class="font-bold text-gray-900">{{ filteredClasses.length }}</span> Kelas
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingClasses" class="py-12 text-center text-gray-400 text-xs">
            Memuat data kelas...
          </div>
          <div v-else>
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3 p-4">
            <div
              v-for="(cls, idx) in filteredClasses"
              :key="cls.id"
              class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs space-y-3"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400">#{{ idx + 1 }}</span>
                    <span class="font-bold text-gray-900 text-sm">{{ cls.name }}</span>
                  </div>
                  <div class="mt-1">
                    <span
                      class="px-2 py-0.5 rounded-md text-[10px] font-bold border inline-block"
                      :class="cls.name.toLowerCase().includes('industri') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-primary/10 text-primary border-primary/20'"
                    >
                      {{ cls.major || 'Umum' }}{{ cls.name.toLowerCase().includes('industri') ? ' (Kelas Industri)' : '' }}
                    </span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="font-bold text-primary text-xs">{{ cls.students_count || 0 }} Siswa</span>
                  <span class="block text-[10px] text-gray-400">{{ cls.schedules_count || 0 }} Mapel</span>
                </div>
              </div>

              <div class="p-3 bg-gray-50/70 rounded-xl space-y-1.5 text-xs text-gray-600">
                <p><span class="text-gray-400">Wali Kelas:</span> <strong class="text-gray-800">{{ cls.advisor?.name || '-' }}</strong></p>
                <p><span class="text-gray-400">Sekretaris:</span> <strong class="text-gray-800">{{ cls.secretary?.name || '-' }}</strong></p>
              </div>

              <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                <button
                  @click="editClass(cls)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                  title="Edit Kelas"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteClass(cls)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  title="Hapus Kelas"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                  <th class="py-3 px-4 whitespace-nowrap">Nama Kelas</th>
                  <th class="py-3 px-4 whitespace-nowrap">Jurusan</th>
                  <th class="py-3 px-4 whitespace-nowrap">Wali Kelas</th>
                  <th class="py-3 px-4 whitespace-nowrap">Sekretaris Kelas</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Jumlah Siswa</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Total Jadwal</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(cls, idx) in filteredClasses" :key="cls.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400 whitespace-nowrap">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900 text-sm whitespace-nowrap">{{ cls.name }}</td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span
                      class="px-2.5 py-1 rounded-lg text-[10px] font-bold border whitespace-nowrap"
                      :class="cls.name.toLowerCase().includes('industri') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-primary/10 text-primary border-primary/20'"
                    >
                      {{ cls.major || 'Umum' }}{{ cls.name.toLowerCase().includes('industri') ? ' (Kelas Industri)' : '' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium whitespace-nowrap">{{ cls.advisor?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap">{{ cls.secretary?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-center font-bold text-primary whitespace-nowrap">{{ cls.students_count || 0 }} Siswa</td>
                  <td class="py-3.5 px-4 text-center font-semibold text-gray-600 whitespace-nowrap">{{ cls.schedules_count || 0 }} Mapel</td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        @click="editClass(cls)"
                        class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                        title="Edit Kelas"
                      >
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="deleteClass(cls)"
                        class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                        title="Hapus Kelas"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 2: MATA PELAJARAN (SUBJECTS)               -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'subjects'" class="space-y-4">
        <!-- Top Row: Title & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Master Mata Pelajaran & Pengajar</h2>
            <p class="text-xs text-gray-400 mt-0.5">Daftar mata pelajaran kejuruan & muatan umum beserta guru pengampunya</p>
          </div>
          <button
            @click="openAddSubjectModal"
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer flex-shrink-0 whitespace-nowrap"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Mapel Baru</span>
          </button>
        </div>

        <!-- Dedicated Filter Bar: Stable Switch & Stats -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-gray-150 shadow-2xs">
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1">Kategori:</span>
            <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl text-xs select-none">
              <button
                type="button"
                @click="filterSubjectByMajor('ALL')"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="subjectMajorFilter === 'ALL' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('RPL')"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="subjectMajorFilter === 'RPL' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                RPL
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('DKV')"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="subjectMajorFilter === 'DKV' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                DKV
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('Animasi')"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="subjectMajorFilter === 'Animasi' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                Animasi
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('UMUM')"
                class="px-3 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                :class="subjectMajorFilter === 'UMUM' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
              >
                Muatan Umum
              </button>
            </div>
          </div>

          <div class="text-xs text-gray-500 font-medium px-2">
            Menampilkan <span class="font-bold text-gray-900">{{ filteredSubjects.length }}</span> dari {{ subjects.length }} Mapel
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingSubjects" class="py-12 text-center text-gray-400 text-xs">
            Memuat data mata pelajaran...
          </div>
          <div v-else>
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3 p-4">
            <div
              v-for="(sub, idx) in filteredSubjects"
              :key="sub.id"
              class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs space-y-3"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400">#{{ idx + 1 }}</span>
                    <span class="font-bold text-gray-900 text-sm">{{ sub.name }}</span>
                  </div>
                  <span class="font-mono text-xs font-bold text-primary">{{ sub.code || '-' }}</span>
                </div>
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0"
                  :class="sub.major === 'UMUM' ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-primary/10 text-primary border border-primary/20'"
                >
                  {{ sub.major === 'UMUM' ? 'Umum' : sub.major }}
                </span>
              </div>

              <div class="p-3 bg-gray-50/70 rounded-xl space-y-1.5 text-xs text-gray-600">
                <p><span class="text-gray-400">Guru Utama:</span> <strong class="text-gray-800">{{ sub.teacher?.name || '-' }}</strong></p>
                <p><span class="text-gray-400">Jadwal Aktif:</span> <strong class="text-gray-800">{{ sub.schedules_count || 0 }} Sesi KBM</strong></p>
              </div>

              <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                <button
                  @click="editSubject(sub)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                  title="Edit Mapel"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteSubject(sub)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  title="Hapus Mapel"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                  <th class="py-3 px-4 whitespace-nowrap">Kode</th>
                  <th class="py-3 px-4 whitespace-nowrap">Nama Mata Pelajaran</th>
                  <th class="py-3 px-4 whitespace-nowrap">Bidang / Kategori</th>
                  <th class="py-3 px-4 whitespace-nowrap">Guru Pengajar Utama</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Jadwal Aktif</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(sub, idx) in filteredSubjects" :key="sub.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400 whitespace-nowrap">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-primary whitespace-nowrap">{{ sub.code || '-' }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">{{ sub.name }}</td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span
                      class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap"
                      :class="sub.major === 'UMUM' ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-primary/10 text-primary border border-primary/20'"
                    >
                      {{ sub.major === 'UMUM' ? 'Muatan Umum' : `${sub.major} Kejuruan` }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium whitespace-nowrap">
                    {{ sub.teacher?.name || '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-center font-semibold text-gray-600 whitespace-nowrap">{{ sub.schedules_count || 0 }} Sesi</td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        @click="editSubject(sub)"
                        class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                        title="Edit Mapel"
                      >
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="deleteSubject(sub)"
                        class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                        title="Hapus Mapel"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 3: JADWAL PELAJARAN (SCHEDULES)            -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'schedules'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Jadwal Mengajar Terpusat</h2>
            <p class="text-xs text-gray-400 mt-0.5">Plotting guru, kelas, hari, jam KBM, dan ruang belajar</p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <select
              v-model="scheduleClassFilter"
              @change="fetchSchedules"
              class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 bg-white cursor-pointer shadow-2xs"
            >
              <option value="">Semua Kelas</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <button
              @click="openAddScheduleModal"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Tambah Jadwal Mengajar</span>
            </button>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingSchedules" class="py-12 text-center text-gray-400 text-xs">
            Memuat data jadwal pelajaran...
          </div>
          <div v-else>
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3 p-4">
            <div
              v-for="(sch, idx) in schedules"
              :key="sch.id"
              class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs space-y-3"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <span class="font-bold text-sm text-gray-900">{{ sch.subject?.name || '-' }}</span>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-xs font-bold text-primary">{{ sch.school_class?.name || '-' }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] text-gray-500 font-medium">{{ sch.room || 'Lab. 1' }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="font-bold text-xs text-gray-900">{{ sch.day }}</span>
                  <span class="block text-[10px] text-gray-500 font-mono">{{ sch.start_time }} - {{ sch.end_time }}</span>
                </div>
              </div>

              <div class="text-xs text-gray-600">
                <span class="text-gray-400">Guru:</span> <strong class="text-gray-800">{{ sch.teacher?.name || '-' }}</strong>
              </div>

              <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                <button
                  @click="editSchedule(sch)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                  title="Edit Jadwal"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteSchedule(sch)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  title="Hapus Jadwal"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                  <th class="py-3 px-4 whitespace-nowrap">Hari & Jam</th>
                  <th class="py-3 px-4 whitespace-nowrap">Kelas</th>
                  <th class="py-3 px-4 whitespace-nowrap">Mata Pelajaran</th>
                  <th class="py-3 px-4 whitespace-nowrap">Guru Pengajar</th>
                  <th class="py-3 px-4 whitespace-nowrap">Ruang</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(sch, idx) in schedules" :key="sch.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400 whitespace-nowrap">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <p class="font-bold text-gray-900">{{ sch.day }}</p>
                    <p class="text-[10px] text-gray-500 font-mono">{{ sch.start_time }} - {{ sch.end_time }}</p>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-primary whitespace-nowrap">{{ sch.school_class?.name || '-' }}</td>
                  <td class="py-3.5 px-4 font-semibold text-gray-800 whitespace-nowrap">{{ sch.subject?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap">{{ sch.teacher?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-500 font-medium whitespace-nowrap">{{ sch.room || 'Lab. 1' }}</td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        @click="editSchedule(sch)"
                        class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                        title="Edit Jadwal"
                      >
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="deleteSchedule(sch)"
                        class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                        title="Hapus Jadwal"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 4: TAHUN AJARAN & SEMESTER (TERMS)         -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'terms'" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Manajemen Tahun Ajaran & Semester</h2>
            <p class="text-xs text-gray-400 mt-0.5">Tentukan kalender pendidikan dan semester yang aktif saat ini</p>
          </div>
          <button
            @click="openAddTermModal"
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Semester Baru</span>
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingTerms" class="py-12 text-center text-gray-400 text-xs">
            Memuat data semester...
          </div>
          <div v-else>
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3 p-4">
            <div
              v-for="(t, idx) in terms"
              :key="t.id"
              class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs space-y-3"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <span class="font-bold text-sm text-gray-900">{{ t.name }}</span>
                  <p class="text-[11px] text-gray-500 font-mono mt-0.5">{{ t.start_date }} s.d. {{ t.end_date }}</p>
                </div>
                <span
                  v-if="t.is_active"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                >
                  ● Sedang Aktif
                </span>
                <span
                  v-else
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-400 border border-gray-200"
                >
                  Arsip
                </span>
              </div>

              <div class="text-xs text-gray-600">
                <span class="text-gray-400">Hari Efektif Belajar:</span> <strong class="text-gray-800">{{ t.effective_days }} Hari</strong>
              </div>

              <div v-if="!t.is_active" class="pt-1 border-t border-gray-100">
                <button
                  @click="activateTerm(t)"
                  class="w-full min-h-[44px] flex items-center justify-center bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-2xs"
                >
                  Set Sebagai Semester Aktif
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                  <th class="py-3 px-4 whitespace-nowrap">Nama Tahun Ajaran & Semester</th>
                  <th class="py-3 px-4 whitespace-nowrap">Rentang Tanggal</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Hari Efektif</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                  <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(t, idx) in terms" :key="t.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400 whitespace-nowrap">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900 text-sm whitespace-nowrap">{{ t.name }}</td>
                  <td class="py-3.5 px-4 text-gray-600 font-mono whitespace-nowrap">{{ t.start_date }} s.d. {{ t.end_date }}</td>
                  <td class="py-3.5 px-4 text-center font-bold text-gray-800 whitespace-nowrap">{{ t.effective_days }} Hari</td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <span
                      v-if="t.is_active"
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap"
                    >
                      ● Sedang Aktif
                    </span>
                    <span
                      v-else
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-400 border border-gray-200 whitespace-nowrap"
                    >
                      Arsip
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <button
                      v-if="!t.is_active"
                      @click="activateTerm(t)"
                      class="px-3 py-1 bg-primary hover:bg-primary-hover text-white rounded-lg text-[10px] font-bold transition cursor-pointer whitespace-nowrap"
                    >
                      Set Sebagai Aktif
                    </button>
                    <span v-else class="text-xs text-emerald-600 font-bold whitespace-nowrap">Aktif Utama</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>
      </div>
    </main>

    <!-- MODAL FORM: KELAS -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingClassId ? 'Edit Kelas' : 'Tambah Kelas' }}</h3>
          <button @click="showClassModal = false" class="text-gray-400 hover:text-gray-600 p-1"><X class="w-3.5 h-3.5" /></button>
        </div>
        <form @submit.prevent="saveClass" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Kelas / Kelas *</label>
            <input v-model="classForm.name" type="text" placeholder="Contoh: XII RPL 1" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Jurusan / Program Keahlian *</label>
            <select v-model="classForm.major" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white font-semibold">
              <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
              <option value="DKV">Desain Komunikasi Visual (DKV)</option>
              <option value="Animasi">Animasi</option>
              <option value="UMUM">Umum</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Wali Kelas</label>
            <select v-model="classForm.advisor_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option value="">- Belum Ditentukan -</option>
              <option v-for="g in teachers" :key="g.id" :value="g.id">{{ g.name }} ({{ g.role }})</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Siswa Sekretaris Kelas</label>
            <select v-model="classForm.secretary_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option value="">- Belum Ditentukan -</option>
              <option v-for="s in candidateSecretaries" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
            </select>
          </div>

          <div class="pt-3 flex justify-end gap-2">
            <button type="button" @click="showClassModal = false" class="px-4 py-2 border rounded-xl text-gray-600">Batal</button>
            <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Menyimpan...' : 'Simpan Kelas' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL FORM: PROMOSI / MUTASI SISWA -->
    <div v-if="showPromoteModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">Mutasi / Pindah Kelas Massal</h3>
          <button @click="showPromoteModal = false" class="text-gray-400 hover:text-gray-600 p-1"><X class="w-3.5 h-3.5" /></button>
        </div>
        <div class="space-y-3.5 text-xs">
          <p class="text-gray-500">Pindahkan seluruh siswa dari satu kelas ke kelas berikutnya.</p>
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Kelas Asal (Sumber)</label>
            <select v-model="promoteForm.source_class_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }} ({{ c.students_count }} Siswa)</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Kelas Tujuan (Target)</label>
            <select v-model="promoteForm.target_class_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="pt-3 flex justify-end gap-2">
            <button type="button" @click="showPromoteModal = false" class="px-4 py-2 border rounded-xl text-gray-600">Batal</button>
            <button @click="submitPromote" :disabled="saving || promoteForm.source_class_id === promoteForm.target_class_id" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Memproses...' : 'Mulai Pindahkan' }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL FORM: MAPEL -->
    <div v-if="showSubjectModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingSubjectId ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h3>
          <button @click="showSubjectModal = false" class="text-gray-400 hover:text-gray-600 p-1"><X class="w-3.5 h-3.5" /></button>
        </div>
        <form @submit.prevent="saveSubject" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Mata Pelajaran *</label>
            <input v-model="subjectForm.name" type="text" placeholder="Contoh: Pemrograman Web & Mobile" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Kode Singkatan</label>
              <input v-model="subjectForm.code" type="text" placeholder="Contoh: RPL-PW" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary uppercase font-mono" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Kategori / Muatan</label>
              <select v-model="subjectForm.major" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
                <option value="RPL">RPL (Rekayasa Perangkat Lunak)</option>
                <option value="DKV">DKV (Desain Komunikasi Visual)</option>
                <option value="Animasi">Animasi</option>
                <option value="UMUM">UMUM (Muatan Umum / Dasar)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Guru Pengajar Utama</label>
            <select v-model="subjectForm.teacher_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option value="">- Belum Ditugaskan -</option>
              <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }} ({{ t.role }})</option>
            </select>
          </div>

          <div class="pt-3 flex justify-end gap-2">
            <button type="button" @click="showSubjectModal = false" class="px-4 py-2 border rounded-xl text-gray-600">Batal</button>
            <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Menyimpan...' : 'Simpan Mapel' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL FORM: JADWAL -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingScheduleId ? 'Edit Jadwal Mengajar' : 'Tambah Jadwal Mengajar' }}</h3>
          <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 p-1"><X class="w-3.5 h-3.5" /></button>
        </div>
        <form @submit.prevent="saveSchedule" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih Kelas</label>
            <select v-model="scheduleForm.class_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Mata Pelajaran</label>
            <select v-model="scheduleForm.subject_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Guru Pengajar</label>
            <select v-model="scheduleForm.teacher_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
              <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Hari</label>
              <select v-model="scheduleForm.day" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
                <option value="Senin">Senin</option>
                <option value="Selasa">Selasa</option>
                <option value="Rabu">Rabu</option>
                <option value="Kamis">Kamis</option>
                <option value="Jumat">Jumat</option>
                <option value="Sabtu">Sabtu</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Ruangan</label>
              <input v-model="scheduleForm.room" type="text" placeholder="Lab. 1" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Jam Mulai</label>
              <input v-model="scheduleForm.start_time" type="time" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Jam Selesai</label>
              <input v-model="scheduleForm.end_time" type="time" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
            </div>
          </div>
          <div class="pt-3 flex justify-end gap-2">
            <button type="button" @click="showScheduleModal = false" class="px-4 py-2 border rounded-xl text-gray-600">Batal</button>
            <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Menyimpan...' : 'Simpan Jadwal' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL FORM: SEMESTER -->
    <div v-if="showTermModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">Tambah Semester & Tahun Ajaran</h3>
          <button @click="showTermModal = false" class="text-gray-400 hover:text-gray-600 p-1"><X class="w-3.5 h-3.5" /></button>
        </div>
        <form @submit.prevent="saveTerm" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Semester</label>
            <input v-model="termForm.name" type="text" placeholder="Contoh: 2026/2027 - Genap" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
              <input v-model="termForm.start_date" type="date" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
              <input v-model="termForm.end_date" type="date" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
            </div>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Jumlah Hari Efektif Belajar</label>
            <input v-model.number="termForm.effective_days" type="number" min="30" max="250" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none" />
          </div>
          <div class="flex items-center gap-2 pt-1">
            <input v-model="termForm.is_active" type="checkbox" id="termActive" class="rounded text-primary" />
            <label for="termActive" class="font-semibold text-gray-700 cursor-pointer">Jadikan semester aktif saat ini</label>
          </div>
          <div class="pt-3 flex justify-end gap-2">
            <button type="button" @click="showTermModal = false" class="px-4 py-2 border rounded-xl text-gray-600">Batal</button>
            <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Menyimpan...' : 'Simpan Semester' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Layers,
  BookOpen,
  Calendar,
  Clock,
  Plus,
  Edit,
  Trash2,
  ArrowRightLeft,
  CheckCircle2,
  AlertCircle,
  X,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'

const tabs = [
  { id: 'classes', label: 'Kelas', icon: Layers },
  { id: 'subjects', label: 'Mata Pelajaran', icon: BookOpen },
  { id: 'schedules', label: 'Jadwal Mengajar', icon: Clock },
  { id: 'terms', label: 'Tahun Ajaran & Semester', icon: Calendar },
]

const activeTab = ref('classes')
const successMsg = ref('')
const errorMsg = ref('')
const saving = ref(false)

const classMajorFilter = ref('ALL')
const subjectMajorFilter = ref('ALL')

// Data states
const classes = ref<any[]>([])
const subjects = ref<any[]>([])
const schedules = ref<any[]>([])
const terms = ref<any[]>([])
const teachers = ref<any[]>([])
const candidateSecretaries = ref<any[]>([])

const uniqueMajors = computed(() => {
  const set = new Set<string>()
  classes.value.forEach((c: any) => {
    if (c.major) set.add(c.major)
  })
  return set.size ? Array.from(set) : ['RPL', 'DKV', 'Animasi']
})

const filteredClasses = computed(() => {
  if (classMajorFilter.value === 'ALL') return classes.value
  return classes.value.filter(c => c.major === classMajorFilter.value)
})

const filteredSubjects = computed(() => {
  if (subjectMajorFilter.value === 'ALL') return subjects.value
  return subjects.value.filter(s => s.major === subjectMajorFilter.value)
})

function filterSubjectByMajor(major: string) {
  subjectMajorFilter.value = major
}

const loadingClasses = ref(false)
const loadingSubjects = ref(false)
const loadingSchedules = ref(false)
const loadingTerms = ref(false)

// Modals
const showClassModal = ref(false)
const editingClassId = ref<number | null>(null)
const classForm = ref({ name: '', major: 'RPL', advisor_id: '', secretary_id: '' })

const showPromoteModal = ref(false)
const promoteForm = ref({ source_class_id: 1, target_class_id: 2 })

const showSubjectModal = ref(false)
const editingSubjectId = ref<number | null>(null)
const subjectForm = ref({ name: '', code: '', major: 'RPL', teacher_id: '' })

const showScheduleModal = ref(false)
const editingScheduleId = ref<number | null>(null)
const scheduleClassFilter = ref('')
const scheduleForm = ref({
  class_id: 1,
  subject_id: 1,
  teacher_id: 1,
  day: 'Senin',
  start_time: '07:30',
  end_time: '09:00',
  room: 'Lab. 1',
})

const showTermModal = ref(false)
const termForm = ref({
  name: '',
  start_date: '2026-07-15',
  end_date: '2026-12-20',
  effective_days: 108,
  is_active: false,
})

// Fetch methods
async function fetchClasses() {
  loadingClasses.value = true
  try {
    const res: any = await $fetch(`${apiBase}/master/classes`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    classes.value = res || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat data kelas'
  } finally {
    loadingClasses.value = false
  }
}

async function fetchSubjects() {
  loadingSubjects.value = true
  try {
    const res: any = await $fetch(`${apiBase}/master/subjects`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    subjects.value = res || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat mata pelajaran'
  } finally {
    loadingSubjects.value = false
  }
}

async function fetchSchedules() {
  loadingSchedules.value = true
  try {
    const url = scheduleClassFilter.value
      ? `${apiBase}/master/schedules?class_id=${scheduleClassFilter.value}`
      : `${apiBase}/master/schedules`
    const res: any = await $fetch(url, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    schedules.value = res || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat jadwal pelajaran'
  } finally {
    loadingSchedules.value = false
  }
}

async function fetchTerms() {
  loadingTerms.value = true
  try {
    const res: any = await $fetch(`${apiBase}/master/terms`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    terms.value = res || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat data semester'
  } finally {
    loadingTerms.value = false
  }
}

async function fetchUsersHelpers() {
  try {
    const res: any = await $fetch(`${apiBase}/users?per_page=100`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const list = res.data?.data || res.data || res || []
    teachers.value = list.filter((u: any) => u.role === 'GURU_PENGAJAR' || u.role === 'GURU_PEMBIMBING')
    candidateSecretaries.value = list.filter((u: any) => u.role === 'SEKRETARIS' || u.role === 'SISWA')
  } catch (e) {
    console.warn(e)
  }
}

// Handlers for Classes
function openAddClassModal() {
  editingClassId.value = null
  classForm.value = { name: '', major: 'RPL', advisor_id: '', secretary_id: '' }
  showClassModal.value = true
}

function editClass(cls: any) {
  editingClassId.value = cls.id
  classForm.value = {
    name: cls.name,
    major: cls.major || 'RPL',
    advisor_id: cls.advisor_id || '',
    secretary_id: cls.secretary_id || '',
  }
  showClassModal.value = true
}

async function saveClass() {
  saving.value = true
  try {
    const method = editingClassId.value ? 'PUT' : 'POST'
    const url = editingClassId.value ? `${apiBase}/master/classes/${editingClassId.value}` : `${apiBase}/master/classes`
    const res: any = await $fetch(url, {
      method,
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: classForm.value,
    })
    successMsg.value = res.message || 'Data kelas berhasil disimpan!'
    showClassModal.value = false
    fetchClasses()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan kelas.'
  } finally {
    saving.value = false
  }
}

async function deleteClass(cls: any) {
  const ok = await confirmDialog({
    title: 'Hapus Kelas?',
    message: `Hapus kelas ${cls.name}? Siswa di dalamnya akan dilepaskan keterikatannya.`,
    confirmText: 'Ya, Hapus Kelas',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${apiBase}/master/classes/${cls.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    successMsg.value = res.message || 'Kelas berhasil dihapus!'
    fetchClasses()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menghapus kelas.'
  }
}

async function submitPromote() {
  saving.value = true
  try {
    const res: any = await $fetch(`${apiBase}/master/classes/promote`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: promoteForm.value
    })
    successMsg.value = res.message || 'Proses pemindahan siswa berhasil!'
    showPromoteModal.value = false
    fetchClasses()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memindahkan siswa.'
  } finally {
    saving.value = false
  }
}

// Handlers for Subjects
function openAddSubjectModal() {
  editingSubjectId.value = null
  subjectForm.value = { name: '', code: '', major: 'RPL', teacher_id: '' }
  showSubjectModal.value = true
}

function editSubject(sub: any) {
  editingSubjectId.value = sub.id
  subjectForm.value = {
    name: sub.name,
    code: sub.code || '',
    major: sub.major || 'RPL',
    teacher_id: sub.teacher_id || '',
  }
  showSubjectModal.value = true
}

async function saveSubject() {
  saving.value = true
  try {
    const method = editingSubjectId.value ? 'PUT' : 'POST'
    const url = editingSubjectId.value ? `${apiBase}/master/subjects/${editingSubjectId.value}` : `${apiBase}/master/subjects`
    const res: any = await $fetch(url, {
      method,
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: subjectForm.value,
    })
    successMsg.value = res.message || 'Mata pelajaran berhasil disimpan!'
    showSubjectModal.value = false
    fetchSubjects()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan mapel.'
  } finally {
    saving.value = false
  }
}

async function deleteSubject(sub: any) {
  const ok = await confirmDialog({
    title: 'Hapus Mata Pelajaran?',
    message: `Apakah Anda yakin ingin menghapus mata pelajaran "${sub.name}"?`,
    confirmText: 'Ya, Hapus Mapel',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${apiBase}/master/subjects/${sub.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    successMsg.value = res.message || 'Mapel berhasil dihapus!'
    fetchSubjects()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menghapus mapel.'
  }
}

// Handlers for Schedules
function openAddScheduleModal() {
  editingScheduleId.value = null
  scheduleForm.value = {
    class_id: classes.value[0]?.id || 1,
    subject_id: subjects.value[0]?.id || 1,
    teacher_id: teachers.value[0]?.id || 1,
    day: 'Senin',
    start_time: '07:30',
    end_time: '09:00',
    room: 'Lab. 1',
  }
  showScheduleModal.value = true
}

function editSchedule(sch: any) {
  editingScheduleId.value = sch.id
  scheduleForm.value = {
    class_id: sch.class_id,
    subject_id: sch.subject_id,
    teacher_id: sch.teacher_id,
    day: sch.day,
    start_time: sch.start_time,
    end_time: sch.end_time,
    room: sch.room,
  }
  showScheduleModal.value = true
}

async function saveSchedule() {
  saving.value = true
  try {
    const method = editingScheduleId.value ? 'PUT' : 'POST'
    const url = editingScheduleId.value ? `${apiBase}/master/schedules/${editingScheduleId.value}` : `${apiBase}/master/schedules`
    const res: any = await $fetch(url, {
      method,
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: scheduleForm.value,
    })
    successMsg.value = res.message || 'Jadwal mengajar berhasil disimpan!'
    showScheduleModal.value = false
    fetchSchedules()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan jadwal.'
  } finally {
    saving.value = false
  }
}

async function deleteSchedule(sch: any) {
  const ok = await confirmDialog({
    title: 'Hapus Jadwal Mengajar?',
    message: 'Apakah Anda yakin ingin menghapus jadwal mengajar ini dari sistem?',
    confirmText: 'Ya, Hapus Jadwal',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${apiBase}/master/schedules/${sch.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    successMsg.value = res.message || 'Jadwal berhasil dihapus!'
    fetchSchedules()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menghapus jadwal.'
  }
}

// Handlers for Terms
function openAddTermModal() {
  termForm.value = {
    name: '',
    start_date: '2026-07-15',
    end_date: '2026-12-20',
    effective_days: 108,
    is_active: false,
  }
  showTermModal.value = true
}

async function saveTerm() {
  saving.value = true
  try {
    const res: any = await $fetch(`${apiBase}/master/terms`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: termForm.value,
    })
    successMsg.value = res.message || 'Semester baru berhasil dibuat!'
    showTermModal.value = false
    fetchTerms()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal membuat semester.'
  } finally {
    saving.value = false
  }
}

async function activateTerm(t: any) {
  const ok = await confirmDialog({
    title: 'Aktifkan Semester?',
    message: `Jadikan "${t.name}" sebagai semester aktif sistem saat ini? Semua transaksi presensi dan jurnal akan mengacu ke semester ini.`,
    confirmText: 'Ya, Aktifkan',
    cancelText: 'Batal',
    type: 'primary',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${apiBase}/master/terms/${t.id}/activate`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    successMsg.value = res.message || 'Semester aktif berhasil diubah!'
    fetchTerms()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengaktifkan semester.'
  }
}

onMounted(() => {
  fetchClasses()
  fetchSubjects()
  fetchSchedules()
  fetchTerms()
  fetchUsersHelpers()
})
</script>
