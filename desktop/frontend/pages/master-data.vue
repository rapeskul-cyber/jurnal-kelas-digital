<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Master Data Akademik"
      subtitle="Kelola rombongan belajar, mata pelajaran, jadwal mengajar, dan tahun ajaran sekolah"
    />

    <main class="px-4 sm:px-6 md:px-8 pb-10 space-y-6 max-w-full overflow-x-hidden min-w-0">
      <!-- Tabs Navigation -->
      <div class="flex items-center gap-4 sm:gap-6 border-b border-gray-200 overflow-x-auto scrollbar-none whitespace-nowrap -mx-1 px-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="activeTab = tab.id"
          class="pb-3 text-xs font-bold transition relative cursor-pointer flex items-center gap-2 flex-shrink-0 whitespace-nowrap"
          :class="activeTab === tab.id ? 'text-primary' : 'text-gray-500 hover:text-gray-800'"
        >
          <component :is="tab.icon" class="w-4 h-4 flex-shrink-0" />
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
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- ============================================== -->
      <!-- TAB 1: ROMBONGAN BELAJAR (KELAS)               -->
      <!-- ============================================== -->
      <div v-if="activeTab === 'classes'" class="space-y-4">
        <!-- Top Row: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Daftar Rombongan Belajar (Kelas)</h2>
            <p class="text-xs text-gray-400 mt-0.5">Kelola kelas per jurusan (RPL, DKV, Animasi), penugasan wali kelas, dan sekretaris</p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <button
              @click="showPromoteModal = true"
              class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
            >
              <ArrowRightLeft class="w-4 h-4 text-gray-500" />
              <span>Mutasi Siswa</span>
            </button>
            <button
              @click="openAddClassModal"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Tambah Rombel Baru</span>
            </button>
          </div>
        </div>

        <!-- Dedicated Filter Bar: Stable Switch & Stats -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-gray-150 shadow-2xs">
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1">Jurusan:</span>
            <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl text-xs select-none">
              <button
                type="button"
                @click="filterClassByMajor('ALL')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer"
                :class="classMajorFilter === 'ALL' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="filterClassByMajor('RPL')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="classMajorFilter === 'RPL' ? 'bg-red-600 text-white shadow-2xs' : 'text-gray-600 hover:text-red-700 hover:bg-red-50'"
              >
                <span class="w-2 h-2 rounded-full bg-red-400 inline-block" v-if="classMajorFilter !== 'RPL'"></span>
                RPL
              </button>
              <button
                type="button"
                @click="filterClassByMajor('DKV')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="classMajorFilter === 'DKV' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-emerald-700 hover:bg-emerald-50'"
              >
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block" v-if="classMajorFilter !== 'DKV'"></span>
                DKV
              </button>
              <button
                type="button"
                @click="filterClassByMajor('Animasi')"
                class="w-18 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="classMajorFilter === 'Animasi' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-amber-700 hover:bg-amber-50'"
              >
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block" v-if="classMajorFilter !== 'Animasi'"></span>
                Animasi
              </button>
            </div>
          </div>

          <div class="text-xs text-gray-500 font-medium px-2">
            Menampilkan <span class="font-bold text-gray-900">{{ filteredClasses.length }}</span> dari {{ classes.length }} Rombel
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingClasses" class="py-12 text-center text-gray-400 text-xs">
            Memuat data rombongan belajar...
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Nama Rombel</th>
                  <th class="py-3 px-4">Jurusan</th>
                  <th class="py-3 px-4">Guru Pembimbing (Wali)</th>
                  <th class="py-3 px-4">Sekretaris Kelas</th>
                  <th class="py-3 px-4 text-center">Jumlah Siswa</th>
                  <th class="py-3 px-4 text-center">Total Jadwal</th>
                  <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(cls, idx) in filteredClasses" :key="cls.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">{{ cls.name }}</td>
                  <td class="py-3.5 px-4">
                    <span
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold"
                      :class="[
                        cls.major === 'RPL' ? 'bg-red-50 text-red-700 border border-red-200' :
                        cls.major === 'DKV' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                        'bg-amber-50 text-amber-800 border border-amber-300'
                      ]"
                    >
                      <img
                        :src="cls.major === 'RPL' ? '/images/logo-rpl.png' : cls.major === 'DKV' ? '/images/logo-dkv.png' : '/images/logo-animasi.png'"
                        :alt="cls.major"
                        class="w-3.5 h-3.5 object-contain"
                      />
                      {{ cls.major || 'RPL' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium">{{ cls.advisor?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-700">{{ cls.secretary?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-center font-bold text-primary">{{ cls.students_count || 0 }} Siswa</td>
                  <td class="py-3.5 px-4 text-center font-semibold text-gray-600">{{ cls.schedules_count || 0 }} Mapel</td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        @click="editClass(cls)"
                        class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                        title="Edit Rombel"
                      >
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="deleteClass(cls)"
                        class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                        title="Hapus Rombel"
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

      <!-- ============================================== -->
      <!-- TAB 2: MATA PELAJARAN (SUBJECTS)               -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'subjects'" class="space-y-4">
        <!-- Top Row: Title & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Master Mata Pelajaran & Pengajar</h2>
            <p class="text-xs text-gray-400 mt-0.5">Daftar mata pelajaran kejuruan (RPL, DKV, Animasi) dan guru pengampunya</p>
          </div>
          <button
            @click="openAddSubjectModal"
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer flex-shrink-0"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Mapel Baru</span>
          </button>
        </div>

        <!-- Dedicated Filter Bar: Stable Switch & Stats -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-gray-150 shadow-2xs">
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1">Jurusan:</span>
            <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl text-xs select-none">
              <button
                type="button"
                @click="filterSubjectByMajor('ALL')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer"
                :class="subjectMajorFilter === 'ALL' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('RPL')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="subjectMajorFilter === 'RPL' ? 'bg-red-600 text-white shadow-2xs' : 'text-gray-600 hover:text-red-700 hover:bg-red-50'"
              >
                <span class="w-2 h-2 rounded-full bg-red-400 inline-block" v-if="subjectMajorFilter !== 'RPL'"></span>
                RPL
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('DKV')"
                class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="subjectMajorFilter === 'DKV' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-emerald-700 hover:bg-emerald-50'"
              >
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block" v-if="subjectMajorFilter !== 'DKV'"></span>
                DKV
              </button>
              <button
                type="button"
                @click="filterSubjectByMajor('Animasi')"
                class="w-18 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                :class="subjectMajorFilter === 'Animasi' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-amber-700 hover:bg-amber-50'"
              >
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block" v-if="subjectMajorFilter !== 'Animasi'"></span>
                Animasi
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
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Kode</th>
                  <th class="py-3 px-4">Nama Mata Pelajaran</th>
                  <th class="py-3 px-4">Jurusan</th>
                  <th class="py-3 px-4">Guru Pengajar Utama</th>
                  <th class="py-3 px-4 text-center">Jadwal Aktif</th>
                  <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(sub, idx) in filteredSubjects" :key="sub.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-primary">{{ sub.code || '-' }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900">{{ sub.name }}</td>
                  <td class="py-3.5 px-4">
                    <span
                      class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold"
                      :class="[
                        sub.major === 'RPL' ? 'bg-red-50 text-red-700 border border-red-200' :
                        sub.major === 'DKV' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                        sub.major === 'Animasi' ? 'bg-amber-50 text-amber-800 border border-amber-300' :
                        'bg-gray-50 text-gray-700 border border-gray-200'
                      ]"
                    >
                      <img
                        v-if="sub.major && sub.major !== 'UMUM'"
                        :src="sub.major === 'RPL' ? '/images/logo-rpl.png' : sub.major === 'DKV' ? '/images/logo-dkv.png' : '/images/logo-animasi.png'"
                        :alt="sub.major"
                        class="w-3 h-3 object-contain"
                      />
                      {{ sub.major || 'UMUM' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium">
                    {{ sub.teacher?.name || '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-center font-semibold text-gray-600">{{ sub.schedules_count || 0 }} Sesi</td>
                  <td class="py-3.5 px-4 text-center">
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
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Hari & Jam</th>
                  <th class="py-3 px-4">Rombel / Kelas</th>
                  <th class="py-3 px-4">Mata Pelajaran</th>
                  <th class="py-3 px-4">Guru Pengajar</th>
                  <th class="py-3 px-4">Ruang</th>
                  <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(sch, idx) in schedules" :key="sch.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4">
                    <p class="font-bold text-gray-900">{{ sch.day }}</p>
                    <p class="text-[10px] text-gray-500 font-mono">{{ sch.start_time }} - {{ sch.end_time }}</p>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-primary">{{ sch.school_class?.name || '-' }}</td>
                  <td class="py-3.5 px-4 font-semibold text-gray-800">{{ sch.subject?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-700">{{ sch.teacher?.name || '-' }}</td>
                  <td class="py-3.5 px-4 text-gray-500 font-medium">{{ sch.room || 'Lab. 1' }}</td>
                  <td class="py-3.5 px-4 text-center">
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
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Semester Baru</span>
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingTerms" class="py-12 text-center text-gray-400 text-xs">
            Memuat data semester...
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Nama Tahun Ajaran & Semester</th>
                  <th class="py-3 px-4">Rentang Tanggal</th>
                  <th class="py-3 px-4 text-center">Hari Efektif</th>
                  <th class="py-3 px-4 text-center">Status</th>
                  <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(t, idx) in terms" :key="t.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3.5 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                  <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">{{ t.name }}</td>
                  <td class="py-3.5 px-4 text-gray-600 font-mono">{{ t.start_date }} s.d. {{ t.end_date }}</td>
                  <td class="py-3.5 px-4 text-center font-bold text-gray-800">{{ t.effective_days }} Hari</td>
                  <td class="py-3.5 px-4 text-center">
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
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <button
                      v-if="!t.is_active"
                      @click="activateTerm(t)"
                      class="px-3 py-1 bg-primary hover:bg-primary-hover text-white rounded-lg text-[10px] font-bold transition cursor-pointer"
                    >
                      Set Sebagai Aktif
                    </button>
                    <span v-else class="text-xs text-emerald-600 font-bold">Aktif Utama</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- MODAL FORM: KELAS -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingClassId ? 'Edit Rombel' : 'Tambah Rombongan Belajar' }}</h3>
          <button @click="showClassModal = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>
        <form @submit.prevent="saveClass" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Rombel / Kelas *</label>
            <input v-model="classForm.name" type="text" placeholder="Contoh: XII RPL 1" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Jurusan / Kompetensi Keahlian *</label>
            <select v-model="classForm.major" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white font-semibold">
              <option value="RPL">RPL (Rekayasa Perangkat Lunak)</option>
              <option value="DKV">DKV (Desain Komunikasi Visual)</option>
              <option value="Animasi">Animasi (2D & 3D)</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Wali Kelas / Guru Pembimbing</label>
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
            <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary text-white font-bold rounded-xl">{{ saving ? 'Menyimpan...' : 'Simpan Rombel' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL FORM: PROMOSI / MUTASI SISWA -->
    <div v-if="showPromoteModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">Mutasi / Pindah Rombel Massal</h3>
          <button @click="showPromoteModal = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>
        <div class="space-y-3.5 text-xs">
          <p class="text-gray-500">Pindahkan seluruh siswa dari satu rombel ke rombel berikutnya (misal kenaikan kelas XI RPL 1 ke XII RPL 1).</p>
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
    <div v-if="showSubjectModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingSubjectId ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h3>
          <button @click="showSubjectModal = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
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
              <label class="block font-semibold text-gray-700 mb-1">Jurusan / Bidang</label>
              <select v-model="subjectForm.major" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none bg-white">
                <option value="RPL">RPL</option>
                <option value="DKV">DKV</option>
                <option value="Animasi">Animasi</option>
                <option value="UMUM">UMUM</option>
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
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">{{ editingScheduleId ? 'Edit Jadwal Mengajar' : 'Tambah Jadwal Mengajar' }}</h3>
          <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
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
    <div v-if="showTermModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">Tambah Semester & Tahun Ajaran</h3>
          <button @click="showTermModal = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
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
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'

const tabs = [
  { id: 'classes', label: 'Rombongan Belajar', icon: Layers },
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

const filteredClasses = computed(() => {
  if (classMajorFilter.value === 'ALL') return classes.value
  return classes.value.filter(c => c.major === classMajorFilter.value)
})

function filterClassByMajor(major: string) {
  classMajorFilter.value = major
}

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
  if (!confirm(`Hapus rombongan belajar ${cls.name}? Siswa di dalamnya akan dilepaskan keterikatannya.`)) return
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
  if (!confirm(`Hapus mata pelajaran ${sub.name}?`)) return
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
  if (!confirm('Hapus jadwal mengajar ini?')) return
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
  if (!confirm(`Jadikan ${t.name} sebagai semester aktif sistem saat ini?`)) return
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
