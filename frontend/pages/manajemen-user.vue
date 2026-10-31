<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Manajemen Pengguna & Hubungan Akun"
      subtitle="Kelola kredensial akun pengguna, alokasi peran sistem, dan sinkronisasi akun orang tua dengan murid"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- Tabs (Daftar Pengguna, Konfigurasi Hak Akses) -->
      <div class="flex items-center gap-6 border-b border-gray-200">
        <button
          type="button"
          @click="activeTab = 'users'"
          class="pb-3 text-xs font-bold transition relative cursor-pointer"
          :class="activeTab === 'users' ? 'text-primary' : 'text-gray-500 hover:text-gray-800'"
        >
          Daftar Pengguna
          <span
            v-if="activeTab === 'users'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"
          ></span>
        </button>

        <button
          type="button"
          @click="activeTab = 'roles'"
          class="pb-3 text-xs font-bold transition relative cursor-pointer"
          :class="activeTab === 'roles' ? 'text-primary' : 'text-gray-500 hover:text-gray-800'"
        >
          Konfigurasi Hak Akses (RBAC)
          <span
            v-if="activeTab === 'roles'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"
          ></span>
        </button>
      </div>

      <!-- Success / Error Alert -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <!-- TAB 1: DAFTAR PENGGUNA -->
      <div v-if="activeTab === 'users'" class="space-y-4">
        <!-- Filter Bar & Tambah User Button -->
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full sm:w-72">
              <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama, NIP, NIS, atau anak..."
                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-2xs"
              />
            </div>

            <!-- Role Dropdown -->
            <div class="relative">
              <select
                v-model="roleFilter"
                class="px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-2xs cursor-pointer"
              >
                <option value="Semua Peran">Semua Peran</option>
                <option value="SUPERADMIN">Super Administrator</option>
                <option value="GURU_PEMBIMBING">Wali Kelas</option>
                <option value="GURU_PENGAJAR">Guru Pengajar</option>
                <option value="SEKRETARIS">Sekretaris Kelas</option>
                <option value="SISWA">Siswa</option>
                <option value="ORANG_TUA">Orang Tua Siswa</option>
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
              <UploadCloud class="w-4 h-4 text-primary" />
              <span>Impor Data Massal</span>
            </button>

            <!-- Tambah Pengguna Baru Button -->
            <button
              type="button"
              @click="showAddDrawer = true"
              class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm hover:shadow cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Tambah Pengguna Baru</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden w-full">
          <div v-if="loadingUsers" class="py-16 text-center text-gray-400 text-xs">
            Memuat data pengguna...
          </div>

          <div v-else-if="filteredUsers.length === 0" class="py-16 text-center text-gray-400 text-xs">
            Tidak ada pengguna yang sesuai dengan filter pencarian.
          </div>

          <div v-else>
            <!-- Mobile Card List View -->
            <div class="block md:hidden space-y-3 p-3">
              <div
                v-for="(u, idx) in filteredUsers"
                :key="'mob-u-' + (u.id || idx)"
                class="p-4 rounded-2xl border border-gray-200 bg-white shadow-2xs space-y-3"
              >
                <div class="flex items-start justify-between gap-2 border-b border-gray-100 pb-2.5">
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-500 font-bold text-xs flex items-center justify-center shrink-0">
                        {{ idx + 1 }}
                      </span>
                      <p class="font-bold text-gray-900 text-sm truncate">{{ u.name }}</p>
                    </div>
                    <p class="text-[11px] text-gray-400 font-mono mt-0.5 ml-8 truncate">{{ u.email }}</p>
                  </div>
                  <span
                    class="px-2.5 py-0.5 rounded-md text-[10px] font-bold shrink-0"
                    :class="{
                      'bg-blue-50 text-primary border border-blue-200': u.role === 'SUPERADMIN',
                      'bg-blue-100 text-primary border border-blue-200': u.role === 'GURU_PEMBIMBING',
                      'bg-blue-50 text-blue-700 border border-blue-200': u.role === 'GURU_PENGAJAR',
                      'bg-sky-50 text-sky-700 border border-sky-200': u.role === 'SEKRETARIS',
                      'bg-sky-50 text-sky-600 border border-sky-100': u.role === 'SISWA',
                      'bg-amber-50 text-amber-800 border border-amber-200': u.role === 'ORANG_TUA',
                    }"
                  >
                    {{ formatRoleLabel(u.role) }}
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50/70 p-2.5 rounded-xl border border-gray-100">
                  <div>
                    <span class="text-gray-400 text-[10px] uppercase block">NIP / NIS</span>
                    <span class="font-mono text-gray-700 font-semibold">{{ u.nip_nis || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-400 text-[10px] uppercase block">Kelas</span>
                    <span class="text-gray-700 font-medium">{{ u.class_name || (u.school_class?.name || '-') }}</span>
                  </div>
                </div>

                <!-- Child connection badge for ORANG_TUA -->
                <div v-if="u.role === 'ORANG_TUA'">
                  <span
                    v-if="u.student"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 w-full"
                  >
                    <GraduationCap class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                    <span class="truncate">Anak: {{ u.student.name }} ({{ u.student.school_class?.name || (u.student.class_id ? 'Kelas ' + u.student.class_id : '-') }})</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 w-full"
                  >
                    Belum terhubung ke anak
                  </span>
                </div>

                <!-- Quick action buttons on mobile with 44px min tap area -->
                <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-gray-100 flex-wrap">
                  <button
                    v-if="authStore.user?.role === 'SUPERADMIN' && u.id !== authStore.user?.id"
                    @click="handleImpersonate(u)"
                    class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 rounded-xl text-xs font-bold transition flex items-center gap-1 min-h-[44px]"
                    title="Masuk sebagai akun ini"
                  >
                    <LogIn class="w-3.5 h-3.5" />
                    <span>Login As</span>
                  </button>
                  <button
                    @click="openEditModal(u)"
                    class="p-2.5 bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 rounded-xl text-xs font-medium transition min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                    title="Edit Data Pengguna"
                  >
                    <Pencil class="w-4 h-4 text-primary" />
                  </button>
                  <button
                    @click="handleResetPassword(u)"
                    class="p-2.5 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl text-xs font-medium transition min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                    title="Reset Sandi"
                  >
                    <KeyRound class="w-4 h-4 text-gray-500" />
                  </button>
                  <button
                    @click="handleToggleActive(u)"
                    class="p-2.5 rounded-xl border text-xs transition min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                    :class="u.is_active !== false ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'"
                  >
                    <Power class="w-4 h-4" />
                  </button>
                  <button
                    v-if="authStore.user?.role === 'SUPERADMIN' && u.id !== authStore.user?.id"
                    @click="handleDeleteUser(u)"
                    class="p-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-medium transition min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                    title="Hapus Akun"
                  >
                    <Trash2 class="w-4 h-4 text-rose-600" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Nama & Akun</th>
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
                    <!-- Child connection badge for ORANG_TUA -->
                    <div v-if="u.role === 'ORANG_TUA'" class="mt-1">
                      <span
                        v-if="u.student"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                      >
                        <GraduationCap class="w-3 h-3 text-emerald-600" />
                        Anak: {{ u.student.name }} ({{ u.student.school_class?.name || (u.student.class_id ? 'Kelas ' + u.student.class_id : '-') }})
                      </span>
                      <span
                        v-else-if="u.specialization"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 text-primary border border-blue-100"
                      >
                        {{ u.specialization }}
                      </span>
                      <span
                        v-else
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                      >
                        Belum terhubung ke anak
                      </span>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 font-mono text-gray-600">
                    {{ u.nip_nis || '-' }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                      :class="{
                        'bg-blue-50 text-primary border border-blue-200': u.role === 'SUPERADMIN',
                        'bg-blue-100 text-primary border border-blue-200': u.role === 'GURU_PEMBIMBING',
                        'bg-blue-50 text-blue-700 border border-blue-200': u.role === 'GURU_PENGAJAR',
                        'bg-sky-50 text-sky-700 border border-sky-200': u.role === 'SEKRETARIS',
                        'bg-sky-50 text-sky-600 border border-sky-100': u.role === 'SISWA',
                        'bg-amber-50 text-amber-800 border border-amber-200': u.role === 'ORANG_TUA',
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
                        class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 rounded-lg text-[10px] font-bold transition flex items-center gap-1 cursor-pointer"
                        title="Masuk sebagai pengguna ini (Impersonate)"
                      >
                        <LogIn class="w-3 h-3" />
                        <span>Masuk As</span>
                      </button>

                      <!-- Edit Pengguna & Hubungan Siswa -->
                      <button
                        @click="openEditModal(u)"
                        class="p-1.5 bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 rounded-lg text-[10px] font-medium transition cursor-pointer"
                        title="Edit Data Pengguna & Hubungan Siswa"
                      >
                        <Pencil class="w-3.5 h-3.5 text-primary" />
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

                      <!-- Delete User (Superadmin) -->
                      <button
                        v-if="authStore.user?.role === 'SUPERADMIN' && u.id !== authStore.user?.id"
                        @click="handleDeleteUser(u)"
                        class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg text-[10px] font-medium transition cursor-pointer"
                        title="Hapus Akun Pengguna"
                      >
                        <Trash2 class="w-3.5 h-3.5 text-rose-600" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        </div>

        <!-- Modal Card Melayang: Tambah Pengguna Baru (Floating Pop-up / Bottom Sheet) -->
        <div
          v-if="showAddDrawer"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
        >
          <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
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
                <XCircle class="w-5 h-5" />
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
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
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
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
                  required
                >
                  <option v-if="authStore.user?.role === 'SUPERADMIN'" value="SUPERADMIN">Super Administrator (Akses Penuh)</option>
                  <option value="GURU_PEMBIMBING">Wali Kelas</option>
                  <option value="GURU_PENGAJAR">Guru Pengajar</option>
                  <option value="SEKRETARIS">Sekretaris Kelas</option>
                  <option value="SISWA">Siswa</option>
                  <option value="ORANG_TUA">Orang Tua Siswa</option>
                </select>
              </div>

              <!-- Hubungkan dengan Siswa (Jika Peran Orang Tua) -->
              <div v-if="newUser.role === 'ORANG_TUA'" class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                <label class="block text-xs font-bold text-primary flex items-center gap-1.5">
                  <GraduationCap class="w-4 h-4 text-primary" />
                  <span>Hubungkan dengan Siswa (Anak Murid) *</span>
                </label>
                <select
                  v-model="newUser.student_id"
                  @change="onStudentSelectForNewUser"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-blue-200 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer"
                  required
                >
                  <option value="">-- Pilih Siswa yang Menjadi Anak --</option>
                  <option v-for="s in studentsList" :key="s.id" :value="s.id">
                    {{ s.name }} (NIS: {{ s.nip_nis || '-' }}) - {{ s.school_class?.name || (s.class_id ? 'Kelas ' + s.class_id : 'Tanpa Kelas') }}
                  </option>
                </select>
                <p class="text-[11px] text-gray-500 leading-relaxed">
                  Akun Orang Tua ini akan otomatis dapat memantau presensi, surat izin, dan jurnal kegiatan belajar harian siswa terpilih.
                </p>
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
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <!-- Alokasi Kelas Utama -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Alokasi Kelas Utama
                </label>
                <select
                  v-model="newUser.class_id"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
                >
                  <option v-for="c in (classesList.length ? classesList : defaultClassesList)" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
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
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
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
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
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
                  class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer text-center"
                >
                  {{ creating ? 'Menyimpan...' : 'Simpan Data' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Card Melayang: Edit Pengguna (Floating Pop-up) -->
        <div
          v-if="showEditDrawer"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
        >
          <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 class="text-base font-bold text-gray-900">Edit Pengguna</h3>
                <p class="text-xs text-gray-400 mt-0.5">Perbarui informasi profil dan penugasan peran</p>
              </div>
              <button
                type="button"
                @click="showEditDrawer = false"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
              >
                <XCircle class="w-5 h-5" />
              </button>
            </div>

            <form @submit.prevent="submitUpdateUser" class="space-y-3.5 text-xs">
              <!-- Nama Lengkap -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nama Lengkap
                </label>
                <input
                  v-model="editUser.name"
                  type="text"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  required
                />
              </div>

              <!-- Peran Sistem -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Peran Sistem
                </label>
                <select
                  v-model="editUser.role"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
                  required
                >
                  <option v-if="authStore.user?.role === 'SUPERADMIN'" value="SUPERADMIN">Super Administrator (Akses Penuh)</option>
                  <option value="GURU_PEMBIMBING">Wali Kelas</option>
                  <option value="GURU_PENGAJAR">Guru Pengajar</option>
                  <option value="SEKRETARIS">Sekretaris Kelas</option>
                  <option value="SISWA">Siswa</option>
                  <option value="ORANG_TUA">Orang Tua Siswa</option>
                </select>
              </div>

              <!-- Hubungkan dengan Siswa (Jika Peran Orang Tua) -->
              <div v-if="editUser.role === 'ORANG_TUA'" class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                <label class="block text-xs font-bold text-primary flex items-center gap-1.5">
                  <GraduationCap class="w-4 h-4 text-primary" />
                  <span>Hubungkan dengan Siswa (Anak Murid) *</span>
                </label>
                <select
                  v-model="editUser.student_id"
                  @change="onStudentSelectForEditUser"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-blue-200 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer"
                  required
                >
                  <option value="">-- Pilih Siswa yang Menjadi Anak --</option>
                  <option v-for="s in studentsList" :key="s.id" :value="s.id">
                    {{ s.name }} (NIS: {{ s.nip_nis || '-' }}) - {{ s.school_class?.name || (s.class_id ? 'Kelas ' + s.class_id : 'Tanpa Kelas') }}
                  </option>
                </select>
                <p class="text-[11px] text-gray-500 leading-relaxed">
                  Menghubungkan akun orang tua ini dengan siswa terpilih untuk melihat data riwayat belajar dan kehadiran anaknya.
                </p>
              </div>

              <!-- Nomor Identitas (NIP / NISN) -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nomor Identitas (NIP / NISN)
                </label>
                <input
                  v-model="editUser.nip_nis"
                  type="text"
                  placeholder="Masukkan nomor identifikasi resmi"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <!-- Alokasi Kelas Utama -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Alokasi Kelas Utama
                </label>
                <select
                  v-model="editUser.class_id"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
                >
                  <option v-for="c in (classesList.length ? classesList : defaultClassesList)" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                  <option value="">- (Tidak Terikat Kelas)</option>
                </select>
              </div>

              <!-- Alamat Email -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Alamat Email
                </label>
                <input
                  v-model="editUser.email"
                  type="email"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  required
                />
              </div>

              <!-- Nomor Telepon -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Nomor Telepon
                </label>
                <input
                  v-model="editUser.phone"
                  type="tel"
                  placeholder="Contoh: 085693233414"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <!-- Action Buttons: Batal & Simpan Perubahan -->
              <div class="pt-3 grid grid-cols-2 gap-3">
                <button
                  type="button"
                  @click="showEditDrawer = false"
                  class="w-full py-2.5 px-4 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer text-center"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="updating"
                  class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer text-center"
                >
                  {{ updating ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Impor Massal Pengguna (Superadmin) -->
        <div
          v-if="showImportModal"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
        >
          <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-primary flex items-center justify-center">
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
                <XCircle class="w-5 h-5" />
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
                  class="w-full p-3 font-mono text-[11px] rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
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
                  class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl font-bold transition disabled:opacity-50 cursor-pointer flex items-center gap-1.5"
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
                <th class="py-3.5 px-4 text-center">Wali Kelas</th>
                <th class="py-3.5 px-4 text-center">Orang Tua</th>
                <th class="py-3.5 px-4 text-center text-primary bg-blue-50/50">Super Administrator</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="rule in rbacMatrix" :key="rule.feature" class="hover:bg-gray-50/50">
                <td class="py-3 px-4 font-semibold text-gray-800">{{ rule.feature }}</td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.siswa ? 'text-primary' : 'text-gray-300'">
                  {{ rule.siswa ? 'Ya' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.sekretaris ? 'text-primary' : 'text-gray-300'">
                  {{ rule.sekretaris ? 'Ya' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.pengajar ? 'text-primary' : 'text-gray-300'">
                  {{ rule.pengajar ? 'Ya' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.pembimbing ? 'text-primary' : 'text-gray-300'">
                  {{ rule.pembimbing ? 'Ya' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="rule.orangtua ? 'text-primary' : 'text-gray-300'">
                  {{ rule.orangtua ? 'Ya' : '—' }}
                </td>
                <td class="py-3 px-4 text-center font-bold text-primary bg-blue-50/20">
                  {{ rule.superadmin ? 'Ya' : '—' }}
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
import { useToastStore } from '~/stores/toast'
import {
  Search,
  Plus,
  XCircle,
  CheckCircle2,
  UploadCloud,
  KeyRound,
  LogIn,
  Power,
  X,
  Pencil,
  Trash2,
  GraduationCap,
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToastStore()

const showImportModal = ref(false)
const importJsonText = ref('')
const importing = ref(false)

const activeTab = ref<'users' | 'roles'>('users')
const loadingUsers = ref(false)
const showAddDrawer = ref(false)
const showEditDrawer = ref(false)
const creating = ref(false)
const updating = ref(false)
const successMsg = ref('')

const searchQuery = ref('')
const roleFilter = ref('Semua Peran')

const studentsList = ref<any[]>([])
const classesList = ref<any[]>([])
const defaultClassesList = [
  { id: 1, name: 'Kelas Industri RPL' },
  { id: 2, name: 'XI RPL 2 (Rekayasa Perangkat Lunak 2)' },
  { id: 3, name: 'XII RPL 1 (Rekayasa Perangkat Lunak 1)' },
  { id: 4, name: 'XII RPL 2 (Rekayasa Perangkat Lunak 2)' },
]

const defaultMockUsers = computed(() => [
  {
    id: authStore.user?.id || 1,
    name: authStore.user?.name || 'Siti Aminah, M.Pd',
    nip_nis: authStore.user?.nip_nis || '19890314002',
    role: authStore.user?.role || 'GURU_PEMBIMBING',
    class_name: '-'
  },
  { id: 101, name: 'Budi Santoso, S.Pd', nip_nis: '19840212001', role: 'GURU_PENGAJAR', class_name: '-' },
  { id: 102, name: 'Rina Safitri', nip_nis: '102401', role: 'SEKRETARIS', class_name: 'Kelas Industri RPL' },
  { id: 103, name: 'Ahmad Fadillah', nip_nis: '102402', role: 'SISWA', class_name: 'Kelas Industri RPL' },
  { id: 104, name: 'Hendra Wijaya', nip_nis: '102403', role: 'SISWA', class_name: 'XI RPL 2' }
])

const users = ref<any[]>([])

const newUser = ref({
  name: '',
  role: 'ORANG_TUA',
  nip_nis: '',
  class_id: '1',
  student_id: '',
  email: '',
  phone: ''
})

const editUser = ref({
  id: null as number | null,
  name: '',
  role: 'SISWA',
  nip_nis: '',
  class_id: '',
  student_id: '',
  email: '',
  phone: ''
})

const formatRoleLabel = (role: string) => {
  switch (role) {
    case 'SUPERADMIN': return 'Super Administrator'
    case 'GURU_PENGAJAR': return 'Guru Pengajar'
    case 'GURU_PEMBIMBING': return 'Wali Kelas'
    case 'ORANG_TUA': return 'Orang Tua Siswa'
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
      (u.nip_nis && u.nip_nis.includes(q)) ||
      (u.email && u.email.toLowerCase().includes(q)) ||
      (u.student?.name && u.student.name.toLowerCase().includes(q)) ||
      (u.specialization && u.specialization.toLowerCase().includes(q))
    return matchRole && matchSearch
  })
})

const fetchStudents = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users?category=SISWA&per_page=200`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const rawData = res.data?.data || res.data || []
    if (Array.isArray(rawData)) {
      studentsList.value = rawData
    }
  } catch (err: any) {
    console.error('Failed to fetch students', err)
  }
}

const fetchClasses = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/classes`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (Array.isArray(res)) {
      classesList.value = res
    }
  } catch (err: any) {
    console.error('Failed to fetch classes', err)
  }
}

function onStudentSelectForNewUser() {
  if (!newUser.value.student_id) return
  const found = studentsList.value.find((s: any) => String(s.id) === String(newUser.value.student_id))
  if (found) {
    if (found.class_id) newUser.value.class_id = String(found.class_id)
    if (found.nip_nis) newUser.value.nip_nis = found.nip_nis
    if (!newUser.value.name || newUser.value.name.startsWith('Orang Tua')) {
      newUser.value.name = `Orang Tua: ${found.name}`
    }
  }
}

function onStudentSelectForEditUser() {
  if (!editUser.value.student_id) return
  const found = studentsList.value.find((s: any) => String(s.id) === String(editUser.value.student_id))
  if (found) {
    if (found.class_id) editUser.value.class_id = String(found.class_id)
    if (found.nip_nis) editUser.value.nip_nis = found.nip_nis
  }
}

function openEditModal(u: any) {
  editUser.value = {
    id: u.id,
    name: u.name,
    role: u.role,
    nip_nis: u.nip_nis || '',
    class_id: u.class_id ? String(u.class_id) : '',
    student_id: u.student_id ? String(u.student_id) : (u.student?.id ? String(u.student.id) : ''),
    email: u.email,
    phone: u.phone || ''
  }
  showEditDrawer.value = true
}

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
      users.value = []
    }
  } catch (err: any) {
    console.error('Error fetching users:', err)
    users.value = []
  } finally {
    loadingUsers.value = false
  }
}

const submitCreateUser = async () => {
  creating.value = true
  successMsg.value = ''
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users`, {
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
        class_id: newUser.value.class_id ? Number(newUser.value.class_id) : null,
        student_id: (newUser.value.role === 'ORANG_TUA' && newUser.value.student_id) ? Number(newUser.value.student_id) : null,
      }
    })

    toast.success(res?.message || `Pengguna baru "${newUser.value.name}" berhasil didaftarkan!`)
    successMsg.value = `Pengguna baru "${newUser.value.name}" berhasil didaftarkan!`
    showAddDrawer.value = false
    newUser.value = {
      name: '',
      role: 'ORANG_TUA',
      nip_nis: '',
      class_id: '1',
      student_id: '',
      email: '',
      phone: ''
    }
    await fetchUsers()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal mendaftarkan pengguna baru.')
  } finally {
    creating.value = false
  }
}

const submitUpdateUser = async () => {
  if (!editUser.value.id) return
  updating.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${editUser.value.id}`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      body: {
        name: editUser.value.name,
        role: editUser.value.role,
        email: editUser.value.email,
        phone: editUser.value.phone,
        nip_nis: editUser.value.nip_nis,
        class_id: editUser.value.class_id ? Number(editUser.value.class_id) : null,
        student_id: (editUser.value.role === 'ORANG_TUA' && editUser.value.student_id) ? Number(editUser.value.student_id) : null,
      }
    })

    toast.success(res?.message || 'Data pengguna berhasil diperbarui.')
    successMsg.value = `Data pengguna "${editUser.value.name}" berhasil diperbarui!`
    showEditDrawer.value = false
    await fetchUsers()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal memperbarui data pengguna.')
  } finally {
    updating.value = false
  }
}

async function handleDeleteUser(user: any) {
  const ok = await confirmDialog({
    title: 'Hapus Akun Pengguna?',
    message: `Apakah Anda yakin ingin menghapus akun ${user.name} (${formatRoleLabel(user.role)})? Tindakan ini tidak dapat dibatalkan.`,
    confirmText: 'Ya, Hapus Akun',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    toast.success(res?.message || 'Pengguna berhasil dihapus.')
    successMsg.value = `Pengguna "${user.name}" berhasil dihapus.`
    await fetchUsers()
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal menghapus pengguna.')
  }
}

const rbacMatrix = [
  { feature: 'Dashboard Statistik & Jadwal', siswa: true, sekretaris: true, pengajar: true, pembimbing: true, orangtua: true, superadmin: true },
  { feature: 'Presensi Mandiri Siswa Harian & Rekap Kelas', siswa: true, sekretaris: true, pengajar: true, pembimbing: true, orangtua: false, superadmin: true },
  { feature: 'Force Unlock Presensi Terkunci', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Rekap Kehadiran Kelas (Jurnal Kelas)', siswa: false, sekretaris: true, pengajar: true, pembimbing: true, orangtua: false, superadmin: true },
  { feature: 'Input & Unggah Materi/Rencana Belajar', siswa: false, sekretaris: false, pengajar: true, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Melihat & Mengunduh Materi Ajar', siswa: true, sekretaris: true, pengajar: true, pembimbing: true, orangtua: true, superadmin: true },
  { feature: 'Jurnal Kegiatan Belajar Mandiri Siswa', siswa: true, sekretaris: true, pengajar: false, pembimbing: true, orangtua: true, superadmin: true },
  { feature: 'Verifikasi & Konfirmasi Jurnal KBM', siswa: false, sekretaris: false, pengajar: true, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Pengajuan Surat Izin Belajar', siswa: true, sekretaris: true, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Persetujuan / Approval Surat Izin', siswa: false, sekretaris: false, pengajar: false, pembimbing: true, orangtua: false, superadmin: true },
  { feature: 'Riwayat & Pantauan Surat Izin Anak', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: true, superadmin: true },
  { feature: 'Rekapitulasi Presensi Semester', siswa: false, sekretaris: false, pengajar: true, pembimbing: true, orangtua: false, superadmin: true },
  { feature: 'Manajemen Pengguna & Tambah Akun Baru', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Impor Pengguna Massal (Excel/JSON)', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Login As / Impersonate Pengguna', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Master Data Akademik (Kelas, Mapel, Jadwal)', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Pusat Broadcast WhatsApp & In-App', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
  { feature: 'Audit Log Explorer & Backup Data', siswa: false, sekretaris: false, pengajar: false, pembimbing: false, orangtua: false, superadmin: true },
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
      toast.warning('Data harus berupa JSON array.')
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
    toast.error(e?.data?.message || 'Format JSON tidak valid atau terjadi kesalahan pada server.')
  } finally {
    importing.value = false
  }
}

async function handleImpersonate(user: any) {
  const ok = await confirmDialog({
    title: 'Masuk Sebagai Pengguna?',
    message: `Apakah Anda yakin ingin masuk sebagai ${user.name} (${formatRoleLabel(user.role)})? Anda akan beralih peran ke akun tersebut.`,
    confirmText: 'Ya, Masuk Sebagai Pengguna',
    cancelText: 'Batal',
    type: 'primary',
  })
  if (!ok) return
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
      toast.success(`Berhasil masuk sebagai ${user.name}! Mengalihkan ke dashboard...`)
      navigateTo('/dashboard')
    }
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal melakukan impersonasi.')
  }
}

async function handleResetPassword(user: any) {
  const ok = await confirmDialog({
    title: 'Reset Kata Sandi?',
    message: `Reset kata sandi pengguna ${user.name} ke kata sandi default ("password123")?`,
    confirmText: 'Ya, Reset Sandi',
    cancelText: 'Batal',
    type: 'warning',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}/reset-password`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    toast.success(res.message || 'Kata sandi berhasil direset.')
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal mereset kata sandi.')
  }
}

async function handleToggleActive(user: any) {
  const action = user.is_active !== false ? 'menonaktifkan' : 'mengaktifkan'
  const ok = await confirmDialog({
    title: `${user.is_active !== false ? 'Nonaktifkan' : 'Aktifkan'} Pengguna?`,
    message: `Apakah Anda yakin ingin ${action} akun ${user.name}?`,
    confirmText: `Ya, ${user.is_active !== false ? 'Nonaktifkan' : 'Aktifkan'}`,
    cancelText: 'Batal',
    type: user.is_active !== false ? 'danger' : 'primary',
  })
  if (!ok) return
  try {
    const res: any = await $fetch(`${config.public.apiBase}/users/${user.id}/toggle-active`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    user.is_active = res.is_active
    successMsg.value = res.message || `Status akun ${user.name} berhasil diperbarui.`
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal mengubah status akun.')
  }
}

onMounted(() => {
  if (authStore.user?.role === 'GURU_PEMBIMBING') {
    return navigateTo('/manajemen-murid')
  }
  fetchUsers()
  fetchStudents()
  fetchClasses()
})
</script>
