<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="pageTitle"
      subtitle="Kelola data siswa, angkat sekretaris kelas (maksimal 2 orang), dan kelola akun orang tua murid"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
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
            <h3 class="text-base font-bold text-gray-900 mt-1 truncate max-w-[200px]" :title="secretaryNames">
              {{ secretaryNames }}
            </h3>
            <p class="text-[11px] font-semibold mt-0.5" :class="secretaries.length >= 2 ? 'text-amber-600' : 'text-primary'">
              {{ secretaries.length }} / 2 Kuota Maksimal Kelas
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary border border-blue-100 flex items-center justify-center font-bold shrink-0">
            <UserCheck class="w-6 h-6" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa Reguler</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ regularStudentCount }} Siswa</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Akun Aktif Presensi</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary border border-blue-100 flex items-center justify-center font-bold">
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
        <button @click="successMsg = ''" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600 flex-shrink-0" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-700 hover:text-rose-900 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
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
              class="px-3 py-1.5 rounded-lg transition cursor-pointer"
              :class="selectedRoleFilter === 'ALL' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
            >
              Semua Murid
            </button>
            <button
              type="button"
              @click="setRoleFilter('SEKRETARIS')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer"
              :class="selectedRoleFilter === 'SEKRETARIS' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
            >
              Sekretaris Kelas ({{ secretaries.length }})
            </button>
            <button
              type="button"
              @click="setRoleFilter('SISWA')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer"
              :class="selectedRoleFilter === 'SISWA' ? 'bg-white text-primary shadow-2xs font-bold border border-blue-200' : 'text-gray-500 hover:text-primary'"
            >
              Siswa Reguler
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Impor Murid Massal Button -->
          <button
            type="button"
            @click="openImportModal"
            class="px-4 py-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs hover:shadow-xs cursor-pointer bg-white"
          >
            <UploadCloud class="w-4 h-4 text-primary" />
            <span>Impor Murid (JSON)</span>
          </button>

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

        <div v-else>
        <!-- Mobile Card List View -->
        <div class="block md:hidden space-y-3.5 p-4">
          <div
            v-for="(s, idx) in filteredStudents"
            :key="s.id"
            class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs space-y-3"
          >
            <!-- Header: Nama, No, Role Badge -->
            <div class="flex items-start justify-between gap-2">
              <div class="space-y-0.5">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-bold text-gray-400">#{{ idx + 1 }}</span>
                  <span class="font-bold text-gray-900 text-sm">{{ s.name }}</span>
                  <span
                    v-if="s.role === 'SEKRETARIS'"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-primary border border-blue-200"
                  >
                    Sekretaris
                  </span>
                </div>
                <div class="text-[11px] text-gray-500 font-mono">NISN: {{ s.nip_nis || '-' }}</div>
              </div>

              <!-- Status Akun Pill -->
              <button
                type="button"
                @click="handleToggleActive(s)"
                class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer shrink-0"
                :class="s.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200'"
              >
                {{ s.is_active ? '● Aktif' : '○ Nonaktif' }}
              </button>
            </div>

            <!-- Kontak & Akun Ortu -->
            <div class="p-3 bg-gray-50/70 rounded-xl space-y-2 text-xs">
              <div class="flex items-center justify-between text-gray-700">
                <span class="text-gray-400 text-[11px]">Email:</span>
                <span class="font-medium truncate max-w-[200px]">{{ s.email }}</span>
              </div>
              <div class="flex items-center justify-between text-gray-700">
                <span class="text-gray-400 text-[11px]">No WA:</span>
                <span class="font-medium">{{ s.phone || '-' }}</span>
              </div>
              <div class="pt-1.5 border-t border-gray-200/60 flex items-center justify-between">
                <span class="text-gray-400 text-[11px]">Akun Ortu:</span>
                <div v-if="s.parent_user" class="flex items-center gap-1.5">
                  <span class="text-emerald-700 font-bold text-[11px]">{{ s.parent_user.name }}</span>
                  <button
                    type="button"
                    @click="openParentModal(s)"
                    class="text-primary hover:text-primary-hover text-[11px] font-semibold underline p-1 cursor-pointer"
                  >
                    Ubah
                  </button>
                </div>
                <div v-else>
                  <button
                    type="button"
                    @click="openParentModal(s)"
                    class="px-2.5 py-1 rounded-lg bg-blue-50 text-primary border border-blue-200 text-[11px] font-bold cursor-pointer"
                  >
                    + Buat Akun Ortu
                  </button>
                </div>
              </div>
            </div>

            <!-- Action Buttons: Role Button & CRUD -->
            <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
              <button
                type="button"
                @click="handleInitiateToggleRole(s)"
                class="flex-1 min-h-[44px] px-3 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
                :class="s.role === 'SEKRETARIS' ? 'bg-blue-100 text-primary' : 'bg-gray-100 text-gray-700 hover:bg-blue-50 hover:text-primary'"
              >
                <UserCheck class="w-4 h-4 shrink-0" />
                <span>{{ s.role === 'SEKRETARIS' ? 'Sekretaris' : 'Jadikan Sekretaris' }}</span>
              </button>

              <div class="flex items-center gap-1">
                <!-- Edit -->
                <button
                  type="button"
                  @click="openEditModal(s)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center hover:bg-gray-100 rounded-xl text-gray-600 transition cursor-pointer"
                  title="Edit Data Siswa"
                >
                  <Edit3 class="w-4 h-4" />
                </button>

                <!-- Reset Password -->
                <button
                  type="button"
                  @click="handleResetPassword(s)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center hover:bg-amber-50 rounded-xl text-amber-600 transition cursor-pointer"
                  title="Reset Kata Sandi"
                >
                  <KeyRound class="w-4 h-4" />
                </button>

                <!-- Delete -->
                <button
                  type="button"
                  @click="handleDelete(s)"
                  class="min-h-[44px] min-w-[44px] flex items-center justify-center hover:bg-rose-50 rounded-xl text-rose-600 transition cursor-pointer"
                  title="Hapus Siswa"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                <th class="py-3 px-4 whitespace-nowrap">Nama Siswa & NISN</th>
                <th class="py-3 px-4 whitespace-nowrap">Kontak Siswa</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Peran Kelas</th>
                <th class="py-3 px-4 whitespace-nowrap">Akun Orang Tua Murid</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Status Akun</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Aksi Cepat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(s, idx) in filteredStudents" :key="s.id" class="hover:bg-gray-50/60 transition">
                <td class="py-3.5 px-4 text-center text-gray-400 font-medium whitespace-nowrap">{{ idx + 1 }}</td>

                <!-- Nama Siswa & NISN -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>{{ s.name }}</span>
                    <span
                      v-if="s.role === 'SEKRETARIS'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-primary border border-blue-200 whitespace-nowrap"
                    >
                      Sekretaris
                    </span>
                  </div>
                  <div class="text-[11px] text-gray-500 font-mono mt-0.5">NISN: {{ s.nip_nis || '-' }}</div>
                </td>

                <!-- Kontak Siswa -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="text-gray-800">{{ s.email }}</div>
                  <div class="text-[11px] text-gray-500 mt-0.5">{{ s.phone ? `WA: ${s.phone}` : 'No HP: -' }}</div>
                </td>

                <!-- Peran Kelas: Siswa vs Sekretaris (Maksimal 2, Minimal 1) -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <button
                    type="button"
                    @click="handleInitiateToggleRole(s)"
                    class="px-3 py-1 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 mx-auto cursor-pointer whitespace-nowrap shadow-2xs"
                    :class="s.role === 'SEKRETARIS' ? 'bg-blue-100 hover:bg-blue-200 text-primary font-bold' : 'bg-gray-100 hover:bg-blue-50 hover:text-primary text-gray-600'"
                    :title="s.role === 'SEKRETARIS' ? 'Klik untuk kembalikan ke Siswa Biasa' : 'Klik untuk jadikan Sekretaris Kelas'"
                  >
                    <UserCheck class="w-3.5 h-3.5" />
                    <span>{{ s.role === 'SEKRETARIS' ? 'Sekretaris' : 'Jadikan Sekretaris' }}</span>
                  </button>
                </td>

                <!-- Akun Orang Tua Murid (Wali Kelas yang Mengelola) -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div v-if="s.parent_user" class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <Check class="w-3 h-3 text-emerald-600" />
                        <span>Terhubung ✓</span>
                      </span>
                      <button
                        type="button"
                        @click="openParentModal(s)"
                        class="text-primary hover:text-primary-hover text-[11px] font-semibold underline cursor-pointer"
                        title="Edit data akun orang tua"
                      >
                        Ubah
                      </button>
                    </div>
                    <div class="font-bold text-gray-800 text-[11px]">{{ s.parent_user.name }}</div>
                    <div class="text-[10px] text-gray-500 font-mono">{{ s.parent_user.phone || s.parent_user.email }}</div>
                  </div>

                  <div v-else class="flex items-center gap-2">
                    <span class="text-gray-400 text-[11px]">Belum Ada</span>
                    <button
                      type="button"
                      @click="openParentModal(s)"
                      class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-primary text-primary hover:text-white border border-blue-200 text-[11px] font-bold transition cursor-pointer shadow-2xs"
                    >
                      + Buat Akun Ortu
                    </button>
                  </div>
                </td>

                <!-- Status Akun -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <button
                    type="button"
                    @click="handleToggleActive(s)"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer whitespace-nowrap"
                    :class="s.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200'"
                  >
                    {{ s.is_active ? '● Aktif' : '○ Nonaktif' }}
                  </button>
                </td>

                <!-- Aksi Cepat -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
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
      </div>
    </main>

    <!-- MODAL KONFIRMASI PENGANGKATAN / PERGANTIAN SEKRETARIS KELAS -->
    <div
      v-if="showSecretaryModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full max-w-md shadow-xl overflow-hidden border border-gray-150 animate-in fade-in duration-150 max-h-[90vh] flex flex-col pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-2 sm:hidden shrink-0"></div>
        <div class="p-5 border-b border-gray-150 flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-gray-900">
              Pengaturan Sekretaris Kelas
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Aturan: Minimal 1 orang, Maksimal 2 orang per kelas</p>
          </div>
          <button @click="showSecretaryModal = false" class="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-5 space-y-4 text-xs">
          <!-- Kasus 1: Kuota penuh (2 sekretaris) -> Wajib memilih siapa yang digantikan -->
          <div v-if="secretaries.length >= 2" class="space-y-3">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 space-y-1">
              <div class="font-bold flex items-center gap-1.5">
                <AlertCircle class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Kuota Sekretaris Penuh (Maksimal 2 Orang)</span>
              </div>
              <p class="text-[11px] leading-relaxed">
                Kelas <strong>{{ className }}</strong> saat ini telah memiliki 2 sekretaris aktif. Untuk mengangkat <strong>{{ targetSecretaryStudent?.name }}</strong>, silakan pilih salah satu sekretaris lama yang akan dikembalikan statusnya menjadi Siswa Biasa:
              </p>
            </div>

            <label class="block font-bold text-gray-700">Pilih Sekretaris yang Akan Digantikan *</label>
            <div class="space-y-2">
              <label
                v-for="sec in secretaries"
                :key="sec.id"
                class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                :class="selectedReplacementId === sec.id ? 'border-primary bg-blue-50/50 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input
                  type="radio"
                  name="replacement_sec"
                  :value="sec.id"
                  v-model="selectedReplacementId"
                  class="text-primary focus:ring-primary"
                />
                <div class="flex-1">
                  <div class="font-bold text-gray-900">{{ sec.name }}</div>
                  <div class="text-[10px] text-gray-500">NISN: {{ sec.nip_nis || '-' }} • Akan dikembalikan menjadi Siswa Biasa</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Kasus 2: Kelas memiliki 1 sekretaris -> Pilihan: tambah jadi ke-2 atau gantikan yang lama -->
          <div v-else-if="secretaries.length === 1" class="space-y-3">
            <p class="text-gray-600 leading-relaxed">
              Kelas saat ini memiliki 1 orang sekretaris aktif (<strong>{{ secretaries[0]?.name }}</strong>).
              Silakan pilih opsi penugasan untuk <strong>{{ targetSecretaryStudent?.name }}</strong>:
            </p>

            <div class="space-y-2">
              <label
                class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition"
                :class="secretaryActionType === 'add_second' ? 'border-primary bg-blue-50/50 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input
                  type="radio"
                  name="sec_action"
                  value="add_second"
                  v-model="secretaryActionType"
                  class="mt-0.5 text-primary focus:ring-primary"
                />
                <div>
                  <div class="font-bold text-gray-900">Tambahkan sebagai Sekretaris ke-2</div>
                  <div class="text-[10px] text-gray-500 mt-0.5">
                    Kelas akan memiliki 2 orang sekretaris: {{ secretaries[0]?.name }} dan {{ targetSecretaryStudent?.name }}.
                  </div>
                </div>
              </label>

              <label
                class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition"
                :class="secretaryActionType === 'replace' ? 'border-primary bg-blue-50/50 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input
                  type="radio"
                  name="sec_action"
                  value="replace"
                  v-model="secretaryActionType"
                  class="mt-0.5 text-primary focus:ring-primary"
                />
                <div>
                  <div class="font-bold text-gray-900">Gantikan Sekretaris Lama</div>
                  <div class="text-[10px] text-gray-500 mt-0.5">
                    <strong>{{ secretaries[0]?.name }}</strong> akan dikembalikan menjadi Siswa Biasa, dan <strong>{{ targetSecretaryStudent?.name }}</strong> menjadi sekretaris tunggal.
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-150 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showSecretaryModal = false"
              class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="confirmSecretaryAssignment"
              :disabled="savingSecretary"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <span>{{ savingSecretary ? 'Menyimpan...' : 'Konfirmasi Penugasan' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL KELOLA AKUN ORANG TUA / WALI MURID (WALI KELAS YANG MENGELOLA) -->
    <div
      v-if="showParentModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full max-w-md shadow-xl overflow-hidden border border-gray-150 animate-in fade-in duration-150 max-h-[90vh] flex flex-col pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-2 sm:hidden shrink-0"></div>
        <div class="p-5 border-b border-gray-150 flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-gray-900">
              Integrasi Akun Orang Tua Murid
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
              Murid: <strong>{{ targetParentStudent?.name }}</strong> (NISN: {{ targetParentStudent?.nip_nis || '-' }})
            </p>
          </div>
          <button @click="showParentModal = false" class="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitParentForm" class="p-5 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-gray-700 mb-1">Nama Orang Tua / Wali Murid *</label>
            <input
              v-model="parentForm.name"
              type="text"
              required
              placeholder="Contoh: Bapak/Ibu Ahmad Fadillah"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-gray-700 mb-1">Nomor WhatsApp Orang Tua *</label>
            <input
              v-model="parentForm.phone"
              type="text"
              required
              placeholder="Contoh: 089621371972"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
            <p class="text-[10px] text-gray-400 mt-1">Digunakan untuk menerima notifikasi presensi mandiri, surat izin, dan OTP WhatsApp.</p>
          </div>

          <div>
            <label class="block font-bold text-gray-700 mb-1">Email Akun Orang Tua (Opsional)</label>
            <input
              v-model="parentForm.email"
              type="email"
              placeholder="ortu.nama@sekolah.sch.id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-gray-700 mb-1">Atur Kata Sandi Baru (Opsional)</label>
            <input
              v-model="parentForm.password"
              type="password"
              placeholder="Kosongkan jika tetap sandi bawaan (password123)"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div class="pt-3 border-t border-gray-150 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showParentModal = false"
              class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingParent"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <span>{{ savingParent ? 'Menyimpan...' : 'Simpan & Hubungkan Akun Ortu' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL TAMBAH / EDIT MURID -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full max-w-md shadow-xl overflow-hidden border border-gray-150 animate-in fade-in duration-150 max-h-[90vh] flex flex-col pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-2 sm:hidden shrink-0"></div>
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
                :class="form.role === 'SEKRETARIS' ? 'border-primary bg-white text-primary font-bold shadow-2xs ring-1 ring-primary/20' : 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                <div class="font-bold">Sekretaris Kelas</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Maks. 2 orang per kelas</div>
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
              <label class="block font-bold text-gray-700 mb-1">No. WhatsApp Siswa</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="081234567890"
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

    <!-- Modal Impor Murid Massal (JSON) -->
    <div
      v-if="showImportModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-xl w-full p-4 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto pb-safe">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="text-base font-bold text-gray-900">Impor Murid Kelas Massal</h3>
            <p class="text-xs text-gray-400 mt-0.5">Unggah berkas JSON atau tempel format data siswa</p>
          </div>
          <button @click="closeImportModal" class="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div v-if="importError" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ importError }}</span>
        </div>

        <div class="space-y-3 text-xs">
          <div class="flex items-center justify-between">
            <label class="font-bold text-gray-700">Format Data Murid (JSON Array)</label>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="downloadStudentTemplate"
                class="text-primary hover:underline font-semibold flex items-center gap-1 cursor-pointer"
              >
                <Download class="w-3.5 h-3.5" />
                <span>Unduh Contoh Template</span>
              </button>
            </div>
          </div>

          <textarea
            v-model="importJsonText"
            rows="8"
            class="w-full font-mono text-[11px] p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50"
            placeholder="[ { &quot;name&quot;: &quot;...&quot;, &quot;nip_nis&quot;: &quot;...&quot;, &quot;email&quot;: &quot;...&quot;, &quot;role&quot;: &quot;SISWA&quot; } ]"
          ></textarea>

          <div class="flex items-center justify-between pt-1">
            <input
              type="file"
              ref="fileInputRef"
              accept=".json"
              class="hidden"
              @change="handleFileUpload"
            />
            <button
              type="button"
              @click="triggerFileInput"
              class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold flex items-center gap-1.5 cursor-pointer"
            >
              <FileCode class="w-3.5 h-3.5 text-primary" />
              <span>{{ uploadedFileName ? uploadedFileName : 'Pilih Berkas .json' }}</span>
            </button>
            <span class="text-gray-400 text-[11px]">Terdeteksi: {{ detectedStudentCount }} data</span>
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeImportModal"
              class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitStudentBulkImport"
              :disabled="importing || !detectedStudentCount"
              class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <span>{{ importing ? 'Mengimpor...' : 'Proses Impor Murid' }}</span>
            </button>
          </div>
        </div>
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
  UploadCloud,
  Download,
  FileCode,
  Check,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useToastStore } from '~/stores/toast'

const authStore = useAuthStore()
const toast = useToastStore()

const students = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)

const showImportModal = ref(false)
const importJsonText = ref('')
const importing = ref(false)
const importError = ref('')
const uploadedFileName = ref('')
const fileInputRef = ref<HTMLInputElement | null>(null)

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

// State Modal Pengaturan Sekretaris
const showSecretaryModal = ref(false)
const targetSecretaryStudent = ref<any>(null)
const selectedReplacementId = ref<number | null>(null)
const secretaryActionType = ref<'add_second' | 'replace'>('add_second')
const savingSecretary = ref(false)

// State Modal Integrasi Orang Tua Murid
const showParentModal = ref(false)
const targetParentStudent = ref<any>(null)
const savingParent = ref(false)
const parentForm = reactive({
  name: '',
  phone: '',
  email: '',
  password: ''
})

const className = computed(() => {
  return authStore.user?.guided_class?.name || authStore.user?.school_class?.name || 'Kelas Industri RPL'
})

const pageTitle = computed(() => {
  return `Manajemen Murid ${className.value.startsWith('Kelas') ? className.value : 'Kelas ' + className.value}`
})

const secretaries = computed(() => students.value.filter(s => s.role === 'SEKRETARIS'))

const secretaryNames = computed(() => {
  if (secretaries.value.length === 0) return 'Belum Ditentukan'
  return secretaries.value.map(s => s.name).join(', ')
})

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
      const matchParent = s.parent_user?.name?.toLowerCase().includes(q)
      return matchName || matchNis || matchEmail || matchParent
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

// ==========================================
// PENGATURAN SEKRETARIS KELAS (MIN 1, MAX 2)
// ==========================================
async function handleInitiateToggleRole(student: any) {
  if (student.role === 'SEKRETARIS') {
    // Upaya menurunkan menjadi SISWA Biasa: Cek batas minimal 1 sekretaris
    if (secretaries.value.length <= 1) {
      toast.error('Kelas wajib memiliki minimal 1 orang sekretaris aktif untuk pengisian presensi dan agenda KBM kelas! Angkat sekretaris pengganti terlebih dahulu sebelum menurunkan siswa ini.')
      return
    }

    const ok = await confirmDialog({
      title: 'Kembalikan ke Siswa Biasa?',
      message: `Ubah peran "${student.name}" menjadi Siswa Biasa? Kelas masih memiliki 1 orang sekretaris lainnya.`,
      confirmText: 'Ya, Kembalikan ke Siswa Biasa',
      cancelText: 'Batal',
      type: 'warning',
    })
    if (!ok) return

    try {
      await $fetch(`${authStore.apiBase}/users/${student.id}`, {
        method: 'PUT',
        headers: { Authorization: `Bearer ${authStore.token}` },
        body: { role: 'SISWA' },
      })
      successMsg.value = `Peran ${student.name} berhasil diubah menjadi Siswa Biasa.`
      fetchClassStudents()
    } catch (e: any) {
      errorMsg.value = e?.data?.message || 'Gagal mengubah peran siswa.'
    }
    return
  }

  // Upaya mengangkat SISWA menjadi SEKRETARIS:
  targetSecretaryStudent.value = student

  if (secretaries.value.length >= 2) {
    // Kuota penuh -> Wajib pilih pengganti
    selectedReplacementId.value = secretaries.value[0]?.id || null
    showSecretaryModal.value = true
    return
  }

  if (secretaries.value.length === 1) {
    // Sudah ada 1 -> Berikan opsi: jadikan ke-2 atau gantikan yang lama
    secretaryActionType.value = 'add_second'
    selectedReplacementId.value = secretaries.value[0]?.id || null
    showSecretaryModal.value = true
    return
  }

  // Jika belum ada sekretaris (0 sekretaris):
  const ok = await confirmDialog({
    title: 'Angkat Sekretaris Kelas?',
    message: `Angkat "${student.name}" sebagai Sekretaris Kelas ${className.value}? Siswa akan mendapatkan akses mengelola presensi harian dan agenda KBM kelas.`,
    confirmText: 'Angkat Jadi Sekretaris',
    cancelText: 'Batal',
    type: 'primary',
  })
  if (!ok) return

  try {
    await $fetch(`${authStore.apiBase}/users/${student.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { role: 'SEKRETARIS' },
    })
    successMsg.value = `Peran ${student.name} berhasil diangkat sebagai Sekretaris Kelas.`
    fetchClassStudents()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengangkat sekretaris kelas.'
  }
}

async function confirmSecretaryAssignment() {
  if (!targetSecretaryStudent.value) return
  savingSecretary.value = true
  try {
    const payload: any = { role: 'SEKRETARIS' }

    if (secretaries.value.length >= 2) {
      payload.replace_secretary_id = selectedReplacementId.value
    } else if (secretaries.value.length === 1 && secretaryActionType.value === 'replace') {
      payload.replace_secretary_id = secretaries.value[0]?.id
    }

    const res: any = await $fetch(`${authStore.apiBase}/users/${targetSecretaryStudent.value.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: payload,
    })

    successMsg.value = res?.message || `Sekretaris kelas berhasil diperbarui.`
    showSecretaryModal.value = false
    await fetchClassStudents()
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal memperbarui sekretaris kelas.')
  } finally {
    savingSecretary.value = false
  }
}

// ==========================================
// INTEGRASI AKUN ORANG TUA OLEH WALI KELAS
// ==========================================
function openParentModal(student: any) {
  targetParentStudent.value = student
  parentForm.name = student.parent_user?.name || ('Bapak/Ibu ' + student.name)
  parentForm.phone = student.parent_user?.phone || student.phone || ''
  parentForm.email = student.parent_user?.email || ''
  parentForm.password = ''
  showParentModal.value = true
}

async function submitParentForm() {
  if (!targetParentStudent.value) return
  if (!parentForm.name || !parentForm.phone) {
    toast.warning('Nama dan Nomor WhatsApp Orang Tua wajib diisi.')
    return
  }
  savingParent.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/users/${targetParentStudent.value.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        parent_name: parentForm.name,
        parent_phone: parentForm.phone,
        parent_email: parentForm.email,
        parent_password: parentForm.password || undefined,
      }
    })

    successMsg.value = res?.message || 'Akun Orang Tua berhasil diintegrasikan!'
    showParentModal.value = false
    await fetchClassStudents()
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal mengintegrasikan akun orang tua.')
  } finally {
    savingParent.value = false
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
  const ok = await confirmDialog({
    title: 'Reset Sandi Murid?',
    message: `Reset kata sandi murid "${student.name}" menjadi default ("password123")?`,
    confirmText: 'Ya, Reset Sandi',
    cancelText: 'Batal',
    type: 'warning',
  })
  if (!ok) return
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
  const ok = await confirmDialog({
    title: 'Hapus Murid Dari Kelas?',
    message: `Apakah Anda yakin ingin menghapus murid "${student.name}" dari kelas ini?`,
    confirmText: 'Ya, Hapus Murid',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return
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

const detectedStudentCount = computed(() => {
  if (!importJsonText.value.trim()) return 0
  try {
    const parsed = JSON.parse(importJsonText.value)
    if (Array.isArray(parsed)) return parsed.length
    if (parsed.users && Array.isArray(parsed.users)) return parsed.users.length
    if (parsed.data && Array.isArray(parsed.data)) return parsed.data.length
    return 0
  } catch {
    return 0
  }
})

function openImportModal() {
  importError.value = ''
  uploadedFileName.value = ''
  if (!importJsonText.value.trim()) {
    fillStudentTemplate()
  }
  showImportModal.value = true
}

function closeImportModal() {
  showImportModal.value = false
  importError.value = ''
}

function triggerFileInput() {
  fileInputRef.value?.click()
}

function handleFileUpload(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  uploadedFileName.value = file.name
  const reader = new FileReader()
  reader.onload = (event) => {
    try {
      const text = event.target?.result as string
      JSON.parse(text)
      importJsonText.value = text
      importError.value = ''
    } catch {
      importError.value = 'Berkas yang diunggah bukan format JSON yang valid.'
    }
  }
  reader.readAsText(file)
}

function fillStudentTemplate() {
  const sample = [
    {
      name: "Ahmad Maulana",
      nip_nis: "102431",
      role: "SISWA",
      phone: "081234567890",
      email: "ahmad.maulana@siswa.sekolah.sch.id"
    },
    {
      name: "Bunga Citra",
      nip_nis: "102432",
      role: "SISWA",
      phone: "081234567891",
      email: "bunga.citra@siswa.sekolah.sch.id"
    },
    {
      name: "Reza Saputra",
      nip_nis: "102433",
      role: "SEKRETARIS",
      phone: "081234567892",
      email: "reza.saputra@siswa.sekolah.sch.id"
    }
  ]
  importJsonText.value = JSON.stringify(sample, null, 2)
  uploadedFileName.value = ''
  importError.value = ''
}

function downloadStudentTemplate() {
  const sample = [
    {
      name: "Contoh Nama Siswa 1",
      nip_nis: "102431",
      role: "SISWA",
      phone: "081234567890",
      email: "siswa1@siswa.sekolah.sch.id"
    },
    {
      name: "Contoh Nama Siswa 2",
      nip_nis: "102432",
      role: "SEKRETARIS",
      phone: "081234567891",
      email: "siswa2@siswa.sekolah.sch.id"
    }
  ]
  const blob = new Blob([JSON.stringify(sample, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `template_siswa_${className.value.toLowerCase().replace(/[^a-z0-9]/g, '_')}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

async function submitStudentBulkImport() {
  importError.value = ''
  let parsed: any = null
  try {
    parsed = JSON.parse(importJsonText.value)
  } catch {
    importError.value = 'Format JSON tidak valid. Periksa tanda koma, kurung kurawal, dan tanda petik.'
    return
  }

  const list = Array.isArray(parsed) ? parsed : (parsed.users || parsed.data || null)
  if (!Array.isArray(list) || !list.length) {
    importError.value = 'Data JSON harus berupa array yang berisi minimal 1 data siswa.'
    return
  }

  importing.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/users/import`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { users: list }
    })
    successMsg.value = res.message || `${list.length} data murid berhasil diimpor ke kelas!`
    showImportModal.value = false
    importJsonText.value = ''
    fetchClassStudents()
  } catch (err: any) {
    importError.value = err?.data?.message || 'Gagal memproses impor massal siswa.'
  } finally {
    importing.value = false
  }
}

onMounted(() => {
  fetchClassStudents()
})
</script>
