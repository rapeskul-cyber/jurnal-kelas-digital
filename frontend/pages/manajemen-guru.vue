<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Manajemen Guru & Staf Pengajar"
      subtitle="Kelola akun wali kelas (wali kelas), guru pengajar mata pelajaran, dan penugasan akademik"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- 3 Stat Cards Ringkasan Guru -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Wali Kelas</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.walas }} Guru</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Membimbing Rombel Kelas</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <Users class="w-6 h-6" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Guru Pengajar (Mapel)</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.pengajar }} Guru</h3>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5">Pengampu Mata Pelajaran</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
            <BookOpen class="w-6 h-6" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Guru & Staf Aktif</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.totalActive }} Akun</h3>
            <p class="text-[11px] text-purple-600 font-medium mt-0.5">Terdaftar di Sistem</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
            <UserCheck class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- Success / Error Alert -->
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

      <!-- Action & Filter Bar -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search -->
          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cari nama, NIP, email, mapel..."
              class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-2xs"
            />
          </div>

          <!-- Role Filter Tabs -->
          <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-semibold">
            <button
              type="button"
              @click="setRoleFilter('ALL')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5"
              :class="selectedRoleFilter === 'ALL' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'"
            >
              <span>Semua Guru</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="selectedRoleFilter === 'ALL' ? 'bg-blue-100 text-primary' : 'bg-gray-200 text-gray-600'"
              >
                {{ stats.walas + stats.pengajar }}
              </span>
            </button>
            <button
              type="button"
              @click="setRoleFilter('GURU_PEMBIMBING')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5"
              :class="selectedRoleFilter === 'GURU_PEMBIMBING' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'text-gray-500 hover:text-emerald-700'"
            >
              <span>Wali Kelas</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="selectedRoleFilter === 'GURU_PEMBIMBING' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
              >
                {{ stats.walas }}
              </span>
            </button>
            <button
              type="button"
              @click="setRoleFilter('GURU_PENGAJAR')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5"
              :class="selectedRoleFilter === 'GURU_PENGAJAR' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-gray-500 hover:text-primary'"
            >
              <span>Guru Pengajar</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="selectedRoleFilter === 'GURU_PENGAJAR' ? 'bg-white/20 text-white' : 'bg-blue-50 text-primary border border-blue-200'"
              >
                {{ stats.pengajar }}
              </span>
            </button>
            <button
              type="button"
              @click="setRoleFilter('SUPERADMIN')"
              class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5"
              :class="selectedRoleFilter === 'SUPERADMIN' ? 'bg-purple-600 text-white font-bold shadow-2xs' : 'text-gray-500 hover:text-purple-700'"
            >
              <span>Super Administrator</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="selectedRoleFilter === 'SUPERADMIN' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-700 border border-purple-200'"
              >
                {{ stats.superadmin }}
              </span>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Impor Guru Massal Button -->
          <button
            type="button"
            @click="openImportModal"
            class="px-4 py-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs hover:shadow-xs cursor-pointer bg-white"
          >
            <UploadCloud class="w-4 h-4 text-primary" />
            <span>Impor Guru (JSON)</span>
          </button>

          <!-- Tambah Guru Button -->
          <button
            type="button"
            @click="openAddModal"
            class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm hover:shadow cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>+ Tambah Guru Baru</span>
          </button>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden w-full">
        <div v-if="loading" class="py-16 text-center text-gray-400 text-xs">
          Memuat data guru & staf...
        </div>

        <div v-else-if="!teachers.length" class="py-16 text-center text-gray-400 text-xs">
          Tidak ada data guru yang sesuai dengan filter pencarian.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-150 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                <th class="py-3 px-4 whitespace-nowrap">Nama Guru & NIP</th>
                <th class="py-3 px-4 whitespace-nowrap">Kontak (Email / WA)</th>
                <th class="py-3 px-4 whitespace-nowrap">Tipe Peran & Penugasan</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Aksi Cepat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(t, idx) in teachers" :key="t.id" class="hover:bg-gray-50/60 transition">
                <td class="py-3.5 px-4 text-center text-gray-400 font-medium whitespace-nowrap">{{ idx + 1 }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-bold text-gray-900 text-sm">{{ t.name }}</div>
                  <div class="text-[11px] text-gray-500 font-mono mt-0.5">NIP: {{ t.nip_nis || '-' }}</div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="text-gray-800">{{ t.email }}</div>
                  <div class="text-[11px] text-gray-500 mt-0.5">{{ t.phone ? `WA: ${t.phone}` : 'No HP: -' }}</div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div v-if="t.role === 'GURU_PEMBIMBING'" class="space-y-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                      Wali Kelas
                    </span>
                    <p class="text-[11px] text-gray-600 font-medium">
                      Wali Kelas: <span class="font-bold text-gray-900">{{ t.guided_class?.name || t.school_class?.name || 'Belum Ditugaskan' }}</span>
                    </p>
                  </div>
                  <div v-else-if="t.role === 'GURU_PENGAJAR'" class="space-y-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">
                      Guru Pengajar
                    </span>
                    <p class="text-[11px] text-gray-600 font-medium">
                      Mapel: <span class="font-bold text-gray-900">{{ t.specialization || (t.taught_subjects?.[0]?.name) || 'Belum Diatur' }}</span>
                    </p>
                  </div>
                  <div v-else-if="t.role === 'SUPERADMIN'" class="space-y-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap">
                      Super Administrator
                    </span>
                    <p class="text-[11px] text-gray-500">Pusat Tata Kelola Sistem</p>
                  </div>
                  <div v-else>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap">
                      {{ t.role }}
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <button
                    type="button"
                    @click="handleToggleActive(t)"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer whitespace-nowrap"
                    :class="t.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200'"
                  >
                    {{ t.is_active ? '● Aktif' : '○ Nonaktif' }}
                  </button>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Impersonate (Login As) -->
                    <button
                      type="button"
                      @click="handleImpersonate(t)"
                      class="px-2.5 py-1 bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-lg text-[11px] font-semibold transition cursor-pointer whitespace-nowrap"
                      title="Masuk sebagai akun guru ini (Impersonasi)"
                    >
                      Login As
                    </button>
                    <!-- Edit -->
                    <button
                      type="button"
                      @click="openEditModal(t)"
                      class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-gray-800 transition cursor-pointer"
                      title="Edit Data Guru"
                    >
                      <Edit3 class="w-4 h-4" />
                    </button>

                    <!-- Reset Password -->
                    <button
                      type="button"
                      @click="handleResetPassword(t)"
                      class="p-1.5 hover:bg-amber-50 rounded-lg text-amber-600 transition cursor-pointer"
                      title="Reset Kata Sandi ke password123"
                    >
                      <KeyRound class="w-4 h-4" />
                    </button>

                    <!-- Delete -->
                    <button
                      type="button"
                      @click="handleDelete(t)"
                      class="p-1.5 hover:bg-rose-50 rounded-lg text-rose-600 transition cursor-pointer"
                      title="Hapus Akun Guru"
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

    <!-- MODAL TAMBAH / EDIT GURU -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden border border-gray-150 animate-in fade-in duration-150">
        <div class="p-5 border-b border-gray-150 flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-gray-900">
              {{ isEditing ? 'Edit Data Akun Guru' : 'Tambah Akun Guru Baru' }}
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Kelola tipe guru, NIP, penugasan rombel atau mapel</p>
          </div>
          <button @click="closeModal" class="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-5 space-y-4 text-xs">
          <!-- Tipe Guru / Peran -->
          <div>
            <label class="block font-bold text-gray-700 mb-1.5">Tipe Guru / Peran Akun *</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <button
                type="button"
                @click="form.role = 'GURU_PEMBIMBING'"
                class="p-3 rounded-xl border text-left transition cursor-pointer"
                :class="form.role === 'GURU_PEMBIMBING' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <div class="font-bold">Wali Kelas</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Wali kelas & rombel</div>
              </button>

              <button
                type="button"
                @click="form.role = 'GURU_PENGAJAR'"
                class="p-3 rounded-xl border text-left transition cursor-pointer"
                :class="form.role === 'GURU_PENGAJAR' ? 'border-blue-500 bg-blue-50 text-blue-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <div class="font-bold">Guru Pengajar</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Guru mapel KBM</div>
              </button>

              <button
                type="button"
                @click="form.role = 'SUPERADMIN'"
                class="p-3 rounded-xl border text-left transition cursor-pointer"
                :class="form.role === 'SUPERADMIN' ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <div class="font-bold">Super Admin</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Admin sistem</div>
              </button>
            </div>
          </div>

          <!-- Informasi Khusus Superadmin -->
          <div v-if="form.role === 'SUPERADMIN'" class="p-3 bg-purple-50 text-purple-800 rounded-xl border border-purple-200">
            <p class="font-bold">Akses Super Administrator</p>
            <p class="text-[11px] mt-0.5 text-purple-700">Akun ini memiliki hak akses penuh untuk mengelola master data, pengaturan sistem, WA Gateway, dan seluruh pengguna.</p>
          </div>

          <!-- Nama Guru -->
          <div>
            <label class="block font-bold text-gray-700 mb-1">Nama Lengkap (Gelar) *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Budi Santoso, S.Pd"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <!-- NIP & No WA -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-gray-700 mb-1">NIP / NUPTK</label>
              <input
                v-model="form.nip_nis"
                type="text"
                placeholder="19840212001"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-gray-700 mb-1">No. WhatsApp</label>
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
            <label class="block font-bold text-gray-700 mb-1">Alamat Email *</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="nama.guru@sekolah.sch.id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <!-- Penugasan Khusus: Wali Kelas (Pilih Rombel / Kelas Binaan) -->
          <div v-if="form.role === 'GURU_PEMBIMBING'">
            <label class="block font-bold text-gray-700 mb-1">Kelas Binaan (Wali Kelas) *</label>
            <select
              v-model="form.class_id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
            >
              <option :value="null">-- Pilih Rombel Kelas --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }} (Jurusan {{ cls.major }})
              </option>
            </select>
          </div>

          <!-- Penugasan Khusus: Guru Pengajar (Spesialisasi Mapel) -->
          <div v-if="form.role === 'GURU_PENGAJAR'" class="space-y-3">
            <div>
              <label class="block font-bold text-gray-700 mb-1">Mata Pelajaran yang Diampu</label>
              <select
                v-model="form.subject_id"
                @change="onSubjectSelect"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
              >
                <option :value="null">-- Pilih Mata Pelajaran --</option>
                <option v-for="s in subjects" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.code }}) - {{ s.major || 'UMUM' }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-gray-700 mb-1">Spesialisasi / Deskripsi Keahlian Guru</label>
              <input
                v-model="form.specialization"
                type="text"
                placeholder="Contoh: Pemrograman Web & Basis Data"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>
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
              <span>{{ saving ? 'Menyimpan...' : (isEditing ? 'Perbarui Guru' : 'Simpan Guru Baru') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Impor Guru & Staf Massal (JSON) -->
    <div
      v-if="showImportModal"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-150 pb-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center font-bold">
              <UploadCloud class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-gray-900">Impor Guru & Staf Pengajar Massal</h3>
              <p class="text-[11px] text-gray-500">Impor akun wali kelas dan pengajar mata pelajaran sekaligus</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeImportModal"
            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4 text-xs">
          <!-- Petunjuk & Tombol Template -->
          <div class="p-4 bg-blue-50/70 border border-blue-150 rounded-2xl space-y-2 text-blue-900">
            <div class="flex items-center justify-between">
              <span class="font-bold flex items-center gap-1.5">
                <FileCode class="w-4 h-4 text-primary" />
                Format Data JSON Guru & Staf:
              </span>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="fillTeacherTemplate"
                  class="text-[11px] font-bold text-primary hover:underline cursor-pointer"
                >
                  Contoh Data
                </button>
                <span class="text-gray-300">•</span>
                <button
                  type="button"
                  @click="downloadTeacherTemplate"
                  class="text-[11px] font-bold text-primary hover:underline flex items-center gap-1 cursor-pointer"
                >
                  <Download class="w-3.5 h-3.5" />
                  Unduh .json
                </button>
              </div>
            </div>
            <p class="text-[11px] text-blue-700 leading-relaxed font-mono bg-white/80 p-2.5 rounded-xl border border-blue-100">
              [{"name": "Nama Guru, S.Kom", "nip_nis": "19900101...", "role": "GURU_PENGAJAR", "specialization": "Pemrograman Web", "email": "guru@sekolah.sch.id"}]
            </p>
            <p class="text-[11px] text-blue-600">
              * Peran yang didukung: <code>GURU_PENGAJAR</code>, <code>GURU_PEMBIMBING</code>, atau <code>SUPERADMIN</code>. Kata sandi otomatis diatur ke <code>password123</code>.
            </p>
          </div>

          <!-- File Upload Zone -->
          <div>
            <label class="block font-bold text-gray-700 mb-1.5">Unggah Berkas .JSON</label>
            <div
              @click="triggerFileInput"
              class="border-2 border-dashed border-gray-200 hover:border-primary/60 hover:bg-blue-50/20 rounded-2xl p-4 text-center cursor-pointer transition flex flex-col items-center justify-center gap-1.5"
            >
              <input
                ref="fileInputRef"
                type="file"
                accept=".json,application/json"
                @change="handleFileUpload"
                class="hidden"
              />
              <UploadCloud class="w-7 h-7 text-gray-400" />
              <div class="text-xs font-semibold text-gray-700">
                <span class="text-primary hover:underline font-bold">Pilih berkas JSON</span> atau seret ke sini
              </div>
              <p class="text-[10px] text-gray-400">Mendukung format .json berisi array data guru & staf pengajar</p>
              <div v-if="uploadedFileName" class="mt-1 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-[11px] flex items-center gap-1">
                <Check class="w-3.5 h-3.5" />
                <span>{{ uploadedFileName }}</span>
              </div>
            </div>
          </div>

          <!-- JSON Editor / Paste Area -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="font-bold text-gray-700">Atau Tempel / Edit Kode JSON di Sini</label>
              <span v-if="detectedTeacherCount > 0" class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                ✓ {{ detectedTeacherCount }} Akun Guru Terdeteksi
              </span>
            </div>
            <textarea
              v-model="importJsonText"
              rows="7"
              placeholder='[
  {
    "name": "Bagas Pratama, S.Kom",
    "nip_nis": "199001012015031001",
    "role": "GURU_PENGAJAR",
    "specialization": "Pemrograman Web & Mobile",
    "email": "bagas.pratama@sekolah.sch.id"
  }
]'
              class="w-full p-3 font-mono text-[11px] rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            ></textarea>
          </div>

          <!-- Alert jika ada error parse / server error -->
          <div v-if="importError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center justify-between">
            <span>{{ importError }}</span>
            <button @click="importError = ''" class="font-bold cursor-pointer">✕</button>
          </div>

          <!-- Modal Actions -->
          <div class="pt-3 border-t border-gray-150 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeImportModal"
              class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              :disabled="importing || !importJsonText.trim()"
              @click="submitTeacherBulkImport"
              class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 shadow-sm"
            >
              <UploadCloud class="w-4 h-4" />
              <span>{{ importing ? 'Mengimpor Data...' : `Mulai Impor (${detectedTeacherCount} Guru)` }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Users,
  UserCheck,
  BookOpen,
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

const authStore = useAuthStore()

const showImportModal = ref(false)
const importJsonText = ref('')
const importing = ref(false)
const importError = ref('')
const uploadedFileName = ref('')
const fileInputRef = ref<HTMLInputElement | null>(null)

const teachers = ref<any[]>([])
const classes = ref<any[]>([])
const subjects = ref<any[]>([])
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
  role: 'GURU_PEMBIMBING',
  class_id: null as number | null,
  subject_id: null as number | null,
  specialization: '',
})

const stats = ref({
  walas: 0,
  pengajar: 0,
  superadmin: 0,
  totalActive: 0
})

async function fetchStats() {
  if (!authStore.token) return
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users?per_page=200`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const list = res.data || []
    stats.value = {
      walas: list.filter((u: any) => u.role === 'GURU_PEMBIMBING').length,
      pengajar: list.filter((u: any) => u.role === 'GURU_PENGAJAR').length,
      superadmin: list.filter((u: any) => u.role === 'SUPERADMIN').length,
      totalActive: list.filter((u: any) => ['GURU_PEMBIMBING', 'GURU_PENGAJAR', 'SUPERADMIN'].includes(u.role) && u.is_active).length,
    }
  } catch (e) {
    console.error('Error fetching stats:', e)
  }
}

async function fetchTeachers() {
  if (!authStore.token) return
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (selectedRoleFilter.value === 'ALL') {
      params.set('category', 'GURU')
    } else {
      params.set('role', selectedRoleFilter.value)
    }
    if (searchQuery.value) params.set('search', searchQuery.value)

    const res = await $fetch<any>(`${authStore.apiBase}/users?${params.toString()}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    teachers.value = res.data || []
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memuat data guru.'
  } finally {
    loading.value = false
  }
}

async function fetchMetadata() {
  if (!authStore.token) return
  try {
    const [clsRes, subRes] = await Promise.all([
      $fetch<any>(`${authStore.apiBase}/master/classes`, { headers: { Authorization: `Bearer ${authStore.token}` } }),
      $fetch<any>(`${authStore.apiBase}/master/subjects`, { headers: { Authorization: `Bearer ${authStore.token}` } }),
    ])
    classes.value = clsRes || []
    subjects.value = subRes || []
  } catch (e) {
    console.error(e)
  }
}

let searchTimer: any = null
function handleSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    fetchTeachers()
  }, 300)
}

function setRoleFilter(role: string) {
  selectedRoleFilter.value = role
  fetchTeachers()
}

function onSubjectSelect() {
  const chosen = subjects.value.find(s => s.id === form.value.subject_id)
  if (chosen && !form.value.specialization) {
    form.value.specialization = chosen.name
  }
}

function openAddModal() {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    email: '',
    nip_nis: '',
    phone: '',
    role: selectedRoleFilter.value === 'ALL' ? 'GURU_PEMBIMBING' : selectedRoleFilter.value,
    class_id: null,
    subject_id: null,
    specialization: '',
  }
  showModal.value = true
}

function openEditModal(teacher: any) {
  isEditing.value = true
  editingId.value = teacher.id
  form.value = {
    name: teacher.name,
    email: teacher.email,
    nip_nis: teacher.nip_nis || '',
    phone: teacher.phone || '',
    role: teacher.role,
    class_id: teacher.guided_class?.id || teacher.class_id || null,
    subject_id: teacher.taught_subjects?.[0]?.id || null,
    specialization: teacher.specialization || '',
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

    successMsg.value = res.message || 'Data guru berhasil disimpan.'
    closeModal()
    fetchTeachers()
    fetchStats()
    fetchMetadata()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan data guru.'
  } finally {
    saving.value = false
  }
}

async function handleToggleActive(teacher: any) {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${teacher.id}/toggle-active`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    teacher.is_active = res.is_active
    successMsg.value = `Status ${teacher.name} diubah menjadi ${res.is_active ? 'Aktif' : 'Nonaktif'}.`
    fetchStats()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengubah status guru.'
  }
}

async function handleResetPassword(teacher: any) {
  if (!confirm(`Reset kata sandi ${teacher.name} menjadi "password123"?`)) return
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${teacher.id}/reset-password`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    successMsg.value = res.message || `Kata sandi ${teacher.name} berhasil direset ke password123.`
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mereset kata sandi.'
  }
}

async function handleImpersonate(teacher: any) {
  if (!confirm(`Masuk sebagai "${teacher.name}" (${teacher.role})? Anda akan diarahkan ke dashboard peran terkait.`)) return
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/users/${teacher.id}/impersonate`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    authStore.token = res.token
    authStore.user = res.user
    localStorage.setItem('token', res.token)
    localStorage.setItem('user', JSON.stringify(res.user))
    navigateTo('/dashboard')
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal melakukan impersonasi.'
  }
}

async function handleDelete(teacher: any) {
  if (!confirm(`Hapus akun guru "${teacher.name}" dari sistem?`)) return
  try {
    await $fetch(`${authStore.apiBase}/users/${teacher.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${authStore.token}` },
    })
    successMsg.value = `Akun guru ${teacher.name} berhasil dihapus.`
    fetchTeachers()
    fetchStats()
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menghapus guru.'
  }
}

const detectedTeacherCount = computed(() => {
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
    fillTeacherTemplate()
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

function fillTeacherTemplate() {
  const sample = [
    {
      name: "Bagas Pratama, S.Kom",
      nip_nis: "199001012015031001",
      role: "GURU_PENGAJAR",
      specialization: "Pemrograman Web & Mobile",
      phone: "081299990001",
      email: "bagas.pratama@sekolah.sch.id"
    },
    {
      name: "Guru Pembimbing, S.Pd",
      nip_nis: "198706152011012001",
      role: "GURU_PEMBIMBING",
      class_name: "Kelas Industri RPL",
      specialization: "Wali Kelas Industri & Konsentrasi RPL",
      phone: "081288990011",
      email: "fadhilah@sekolah.sch.id"
    },
    {
      name: "Veranika TM, S.Kom",
      nip_nis: "198805052014022002",
      role: "GURU_PENGAJAR",
      specialization: "Basis Data & Cloud",
      phone: "081299990003",
      email: "veranika@sekolah.sch.id"
    }
  ]
  importJsonText.value = JSON.stringify(sample, null, 2)
  uploadedFileName.value = ''
  importError.value = ''
}

function downloadTeacherTemplate() {
  const sample = [
    {
      name: "Contoh Guru Pengajar, S.Kom",
      nip_nis: "199001012015031001",
      role: "GURU_PENGAJAR",
      specialization: "Pemrograman Berorientasi Objek",
      phone: "081299990001",
      email: "guru1@sekolah.sch.id"
    },
    {
      name: "Contoh Wali Kelas, S.Pd",
      nip_nis: "198706152011012001",
      role: "GURU_PEMBIMBING",
      class_name: "Kelas Industri RPL",
      specialization: "Wali Kelas & Konsentrasi RPL",
      phone: "081299990002",
      email: "guru2@sekolah.sch.id"
    }
  ]
  const blob = new Blob([JSON.stringify(sample, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `template_guru_staf_pengajar.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

async function submitTeacherBulkImport() {
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
    importError.value = 'Data JSON harus berupa array yang berisi minimal 1 data guru/staf.'
    return
  }

  importing.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/users/import`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { users: list }
    })
    successMsg.value = res.message || `${list.length} data guru/staf berhasil diimpor!`
    showImportModal.value = false
    importJsonText.value = ''
    fetchTeachers()
    fetchMetadata()
  } catch (err: any) {
    importError.value = err?.data?.message || 'Gagal memproses impor massal guru.'
  } finally {
    importing.value = false
  }
}

onMounted(() => {
  fetchTeachers()
  fetchMetadata()
  fetchStats()
})
</script>
