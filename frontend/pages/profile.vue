<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Profil Pengguna"
      subtitle="Kelola informasi akun, nomor WhatsApp OTP, dan keamanan kata sandi"
    />

    <main class="px-4 sm:px-8 pb-10 max-w-4xl space-y-5">
      <!-- Profile Overview Card -->
      <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar Photo & Upload/Ganti/Hapus Controls -->
        <div class="flex flex-col items-center gap-3 flex-shrink-0">
          <div class="relative group">
            <div class="w-24 h-24 rounded-full overflow-hidden bg-blue-50 border-2 border-blue-200 ring-4 ring-blue-50/50 flex items-center justify-center shadow-inner">
              <img
                v-if="authStore.user?.avatar || authStore.user?.avatar_url"
                :src="authStore.user?.avatar || authStore.user?.avatar_url"
                alt="Foto Profil"
                class="w-full h-full object-cover"
              />
              <span v-else class="text-3xl font-black text-primary select-none">
                {{ userInitial }}
              </span>
            </div>

            <!-- Hover overlay button to trigger file input -->
            <button
              type="button"
              @click="triggerFileInput"
              title="Unggah / Ganti Foto Profil"
              class="absolute inset-0 bg-black/45 text-white rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-150 cursor-pointer"
            >
              <Camera class="w-6 h-6 text-white mb-0.5" />
              <span class="text-[10px] font-semibold">Ganti Foto</span>
            </button>
          </div>

          <!-- Action buttons underneath avatar (White background, grey text, turns blue on click/hover) -->
          <div class="flex items-center gap-2">
            <input
              type="file"
              ref="fileInputRef"
              accept="image/png,image/jpeg,image/jpg,image/webp"
              class="hidden"
              @change="handleAvatarSelected"
            />
            <button
              type="button"
              @click="triggerFileInput"
              :disabled="uploadingAvatar"
              class="px-3 py-1.5 text-xs font-semibold text-gray-500 bg-white hover:text-primary hover:border-blue-200 active:text-primary border border-gray-200 rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Upload class="w-3.5 h-3.5" />
              <span>{{ uploadingAvatar ? 'Mengunggah...' : (authStore.user?.avatar || authStore.user?.avatar_url ? 'Ganti Foto' : 'Unggah Foto') }}</span>
            </button>

            <button
              v-if="authStore.user?.avatar || authStore.user?.avatar_url"
              type="button"
              @click="handleDeleteAvatar"
              :disabled="uploadingAvatar"
              class="px-3 py-1.5 text-xs font-semibold text-gray-500 bg-white hover:text-rose-600 hover:border-rose-200 active:text-rose-600 border border-gray-200 rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              title="Hapus Foto Profil"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span>Hapus</span>
            </button>
          </div>
        </div>

        <div class="flex-1 text-center sm:text-left space-y-1">
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
            <h2 class="text-xl font-bold text-gray-900">{{ authStore.user?.name || 'Pengguna' }}</h2>
          </div>
          <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
          <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-gray-600">
            <span v-if="authStore.user?.nip_nis" class="flex items-center gap-1.5 font-medium">
              <span class="text-gray-400">NIS/NIP:</span> {{ authStore.user.nip_nis }}
            </span>
            <span v-if="authStore.user?.school_class" class="flex items-center gap-1.5 font-medium">
              <span class="text-gray-400">Kelas Binaan:</span> {{ authStore.user.school_class.name }}
            </span>
            <span class="flex items-center gap-1.5 font-medium">
              <span class="text-gray-400">Status:</span>
              <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
              </span>
            </span>
          </div>
        </div>
      </div>

      <!-- Form Edit Profil & Kontak WhatsApp -->
      <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs space-y-5">
        <div class="border-b border-gray-100 pb-3">
          <h3 class="text-sm font-bold text-gray-900">Informasi Pribadi & Kontak</h3>
          <p class="text-xs text-gray-500 mt-0.5">Nomor WhatsApp digunakan untuk verifikasi pengiriman kode OTP lupa kata sandi.</p>
        </div>

        <div v-if="successMsg" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
          {{ successMsg }}
        </div>
        <div v-if="errorMsg" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Alamat Email (Akun)</label>
              <input
                :value="authStore.user?.email"
                disabled
                type="email"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-xs cursor-not-allowed outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nomor WhatsApp (Tujuan OTP)</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="Contoh: 08123456789 atau 628123456789"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none"
              />
              <p class="text-[11px] text-gray-400 mt-1">Pastikan nomor aktif dan terdaftar di WhatsApp.</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Role / Peran Sistem</label>
              <input
                :value="roleLabel"
                disabled
                type="text"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-xs cursor-not-allowed outline-none"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
              <h4 class="text-xs font-bold text-gray-900">Ubah Kata Sandi (Opsional)</h4>
              <button
                type="button"
                @click="openSmsOtpModal"
                class="text-[11px] text-primary hover:underline font-semibold flex items-center gap-1 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Lupa kata sandi lama? Coba cara lain via WhatsApp OTP</span>
              </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs text-gray-600 mb-1">Kata Sandi Saat Ini</label>
                <div class="relative">
                  <input
                    v-model="form.old_password"
                    :type="showOldPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2 pr-10 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none"
                  />
                  <button
                    type="button"
                    @click="showOldPassword = !showOldPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                    tabindex="-1"
                    title="Tampilkan / Sembunyikan Kata Sandi"
                  >
                    <EyeOff v-if="showOldPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-xs text-gray-600 mb-1">Kata Sandi Baru</label>
                <div class="relative">
                  <input
                    v-model="form.new_password"
                    :type="showNewPassword ? 'text' : 'password'"
                    placeholder="Min. 8 karakter"
                    class="w-full px-3.5 py-2 pr-10 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                    tabindex="-1"
                    title="Tampilkan / Sembunyikan Kata Sandi"
                  >
                    <EyeOff v-if="showNewPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-xs text-gray-600 mb-1">Konfirmasi Sandi Baru</label>
                <div class="relative">
                  <input
                    v-model="form.new_password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="Ulangi sandi baru"
                    class="w-full px-3.5 py-2 pr-10 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                    tabindex="-1"
                    title="Tampilkan / Sembunyikan Kata Sandi"
                  >
                    <EyeOff v-if="showConfirmPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-3 flex justify-end">
            <button
              :disabled="saving"
              type="submit"
              class="px-5 py-2.5 rounded-xl bg-primary hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Modal Coba Cara Lain via WhatsApp Gateway OTP -->
      <div v-if="showSmsModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-4 sm:p-6 shadow-xl border border-gray-150 space-y-4 animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto pb-safe">
          <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-2 sm:hidden shrink-0"></div>
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              </div>
              <div>
                <h3 class="text-sm font-bold text-gray-900">Ubah Kata Sandi via WhatsApp</h3>
                <p class="text-[11px] text-gray-500">Verifikasi kode OTP melalui WhatsApp terdaftar</p>
              </div>
            </div>
            <button type="button" @click="closeSmsModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div v-if="smsError" class="p-3 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs">
            {{ smsError }}
          </div>
          <div v-if="smsSuccess" class="p-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs">
            {{ smsSuccess }}
          </div>

          <!-- Step 1: Send WhatsApp OTP -->
          <div v-if="smsStep === 1" class="space-y-4">
            <p class="text-xs text-gray-600 leading-relaxed">
              Jika Anda lupa kata sandi lama, kami akan mengirimkan 6 digit kode OTP verifikasi resmi langsung ke WhatsApp Anda:
            </p>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-800 font-medium flex items-center justify-between">
              <span>Nomor WhatsApp Akun:</span>
              <span v-if="authStore.user?.phone || form.phone" class="font-bold font-mono text-gray-900">
                {{ authStore.user?.phone || form.phone }}
              </span>
              <span v-else class="text-rose-600 font-bold text-xs">
                Belum Terdaftar di Profil
              </span>
            </div>
            <p v-if="!authStore.user?.phone && !form.phone" class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
              Perhatian: Anda belum mengisi nomor WhatsApp. Silakan tutup jendela ini, isi nomor WhatsApp Anda pada formulir profil di atas, dan klik Simpan Perubahan terlebih dahulu.
            </p>
            <button
              type="button"
              :disabled="smsLoading || (!authStore.user?.phone && !form.phone)"
              @click="requestSmsOtp"
              class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              <span>{{ smsLoading ? 'Mengirim ke WhatsApp...' : 'Kirim Kode OTP via WhatsApp' }}</span>
            </button>
          </div>

          <!-- Step 2: Verify OTP -->
          <div v-else-if="smsStep === 2" class="space-y-4">
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 leading-relaxed">
              Pesan verifikasi telah dikirim ke nomor WhatsApp <strong>{{ smsMaskedPhone }}</strong>. Buka aplikasi WhatsApp Anda untuk melihat 6 digit kode OTP.
            </div>
            <div v-if="smsOtpPreview" class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center justify-between">
              <span>Mode Simulasi (OTP): <strong>{{ smsOtpPreview }}</strong></span>
              <button type="button" @click="smsOtpInput = smsOtpPreview" class="text-primary font-bold hover:underline cursor-pointer">Gunakan</button>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Kode OTP WhatsApp (6 Digit)</label>
              <input
                v-model="smsOtpInput"
                type="text"
                maxlength="6"
                placeholder="123456" inputmode="numeric" pattern="[0-9]*"
                class="w-full text-center text-lg font-mono font-bold tracking-widest px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
              />
            </div>
            <div class="flex gap-2">
              <button
                type="button"
                :disabled="smsLoading || smsOtpInput.length !== 6"
                @click="verifySmsOtp"
                class="flex-1 py-2.5 bg-primary hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition disabled:opacity-50 cursor-pointer"
              >
                {{ smsLoading ? 'Memverifikasi...' : 'Verifikasi Kode OTP' }}
              </button>
              <button
                type="button"
                :disabled="smsCooldown > 0 || smsLoading"
                @click="requestSmsOtp"
                class="px-3 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl text-xs font-medium transition disabled:opacity-50 cursor-pointer"
              >
                {{ smsCooldown > 0 ? `${smsCooldown}s` : 'Kirim Ulang WA' }}
              </button>
            </div>
          </div>

          <!-- Step 3: Enter New Password -->
          <div v-else-if="smsStep === 3" class="space-y-4">
            <p class="text-xs text-gray-600 leading-relaxed">
              Verifikasi WhatsApp berhasil! Silakan tentukan kata sandi baru untuk akun Anda:
            </p>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
              <div class="relative">
                <input
                  v-model="smsNewPassword"
                  :type="showSmsNewPassword ? 'text' : 'password'"
                  placeholder="Min. 8 karakter"
                  class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                />
                <button
                  type="button"
                  @click="showSmsNewPassword = !showSmsNewPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                  tabindex="-1"
                  title="Tampilkan / Sembunyikan Kata Sandi"
                >
                  <EyeOff v-if="showSmsNewPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
              <div class="relative">
                <input
                  v-model="smsNewPasswordConfirmation"
                  :type="showSmsConfirmPassword ? 'text' : 'password'"
                  placeholder="Ulangi kata sandi baru"
                  class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                />
                <button
                  type="button"
                  @click="showSmsConfirmPassword = !showSmsConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                  tabindex="-1"
                  title="Tampilkan / Sembunyikan Kata Sandi"
                >
                  <EyeOff v-if="showSmsConfirmPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>
            <button
              type="button"
              :disabled="smsLoading || !smsNewPassword || smsNewPassword !== smsNewPasswordConfirmation"
              @click="submitNewPasswordViaSms"
              class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              {{ smsLoading ? 'Menyimpan Sandi Baru...' : 'Simpan Kata Sandi Baru' }}
            </button>
          </div>
        </div>
      </div>

      <!-- WhatsApp Gateway Integration Box (Eksklusif SUPERADMIN) -->
      <div v-if="authStore.user?.role === 'SUPERADMIN'" class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Koneksi WhatsApp Gateway (Fonte Mandiri)</h3>
            <p class="text-xs text-gray-500 mt-0.5">Tautkan nomor WhatsApp sekolah untuk pengiriman OTP & Auto-Funnel tanpa bayar.</p>
          </div>
          <span
            :class="(waStatus.status === 'connected' || waStatus.connected) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
            class="text-[11px] font-bold px-2.5 py-1 rounded-full border"
          >
            {{ (waStatus.status === 'connected' || waStatus.connected) ? '● WhatsApp Terhubung' : '○ Belum Terhubung' }}
          </span>
        </div>

        <div v-if="waStatus.status !== 'connected' && !waStatus.connected" class="space-y-3">
          <p class="text-xs text-gray-600">
            Ketik nomor WhatsApp yang ingin dijadikan bot/pengirim OTP, lalu klik <strong>Minta Kode Pairing</strong>:
          </p>
          <div class="flex gap-2">
            <input
              v-model="waPhoneInput"
              type="text"
              placeholder="Contoh: 088298674462"
              class="flex-1 px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
            />
            <button
              type="button"
              :disabled="requestingWa || !waPhoneInput"
              @click="requestWaPairing"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition disabled:opacity-50 cursor-pointer"
            >
              {{ requestingWa ? 'Meminta Kode...' : 'Minta Kode Pairing' }}
            </button>
          </div>

          <div v-if="pairingCode" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-center space-y-1">
            <p class="text-xs text-emerald-800">Kode Pairing WhatsApp Anda:</p>
            <div class="text-3xl font-mono font-black tracking-widest text-emerald-700 select-all py-1">
              {{ pairingCode }}
            </div>
            <p class="text-[11px] text-emerald-600">
              Buka WhatsApp di HP → <strong>Perangkat Tertaut</strong> → <strong>Tautkan dengan nomor telepon</strong> → Masukkan kode di atas.
            </p>
          </div>
        </div>

        <div v-else class="flex items-center justify-between p-3 bg-emerald-50/50 border border-emerald-100 rounded-xl">
          <div class="text-xs text-emerald-800">
            Bot terhubung dengan nomor: <strong>{{ waStatus.user?.id || 'Aktif' }}</strong>
          </div>
          <button
            type="button"
            @click="logoutWa"
            class="px-3 py-1 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-lg transition"
          >
            Putuskan Koneksi
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Camera, Upload, Trash2, Eye, EyeOff } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useToastStore } from '~/stores/toast'
import { ROLE_LABELS } from '~/config/access'

const showOldPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const showSmsNewPassword = ref(false)
const showSmsConfirmPassword = ref(false)

const fileInputRef = ref<HTMLInputElement | null>(null)
const uploadingAvatar = ref(false)

function triggerFileInput() {
  fileInputRef.value?.click()
}

async function handleAvatarSelected(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    toast.error('Berkas harus berupa gambar (JPG, PNG, WEBP).')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    toast.error('Ukuran foto maksimal 5 MB.')
    return
  }

  uploadingAvatar.value = true
  try {
    const formData = new FormData()
    formData.append('avatar', file)

    const res: any = await $fetch(`${authStore.apiBase}/auth/profile/avatar`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      body: formData,
    })

    if (res.user) {
      authStore.user = res.user
      if (import.meta.client) {
        localStorage.setItem('user', JSON.stringify(res.user))
      }
    } else if (res.avatar_url && authStore.user) {
      authStore.user.avatar = res.avatar_url
      authStore.user.avatar_url = res.avatar_url
      if (import.meta.client) {
        localStorage.setItem('user', JSON.stringify(authStore.user))
      }
    }
    toast.success(res.message || 'Foto profil berhasil diperbarui!')
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal mengunggah foto profil.')
  } finally {
    uploadingAvatar.value = false
    if (fileInputRef.value) {
      fileInputRef.value.value = ''
    }
  }
}

async function handleDeleteAvatar() {
  const ok = await confirmDialog({
    title: 'Hapus Foto Profil?',
    message: 'Foto profil Anda akan dihapus permanen dan digantikan dengan avatar inisial nama standar.',
    confirmText: 'Ya, Hapus Foto',
    cancelText: 'Batal',
    type: 'danger',
  })
  if (!ok) return

  uploadingAvatar.value = true
  try {
    const res: any = await $fetch(`${authStore.apiBase}/auth/profile/avatar`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    })

    if (res.user) {
      authStore.user = res.user
      if (import.meta.client) {
        localStorage.setItem('user', JSON.stringify(res.user))
      }
    } else if (authStore.user) {
      authStore.user.avatar = null
      authStore.user.avatar_url = null
      if (import.meta.client) {
        localStorage.setItem('user', JSON.stringify(authStore.user))
      }
    }
    toast.success(res.message || 'Foto profil berhasil dihapus!')
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal menghapus foto profil.')
  } finally {
    uploadingAvatar.value = false
  }
}

const authStore = useAuthStore()
const toast = useToastStore()

const form = ref({
  name: authStore.user?.name || '',
  phone: authStore.user?.phone || '',
  old_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

// WhatsApp Gateway state
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'
const waStatus = ref<any>({ status: 'disconnected' })
const waPhoneInput = ref('')
const pairingCode = ref('')
const requestingWa = ref(false)

const fetchWaStatus = async () => {
  if (authStore.user?.role !== 'SUPERADMIN') return
  try {
    const res: any = await $fetch(`${apiBase}/wa-gateway/status`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res) {
      const data = res.data || res
      waStatus.value = data
      if (data.status === 'connected' || data.connected === true) {
        pairingCode.value = ''
      }
    }
  } catch (e) {
    console.warn('Gagal memuat status WhatsApp gateway:', e)
    waStatus.value = { status: 'disconnected', connected: false }
  }
}

const requestWaPairing = async () => {
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
    } else if (res && res.message) {
      toast.success(res.message)
    }
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal meminta kode pairing WhatsApp')
  } finally {
    requestingWa.value = false
    fetchWaStatus()
  }
}

const logoutWa = async () => {
  const ok = await confirmDialog({
    title: 'Putuskan WhatsApp?',
    message: 'Sesi bot WhatsApp ini akan diputus. Notifikasi otomatis tidak dapat dikirim sebelum ditautkan kembali.',
    confirmText: 'Ya, Putuskan',
    cancelText: 'Batal',
    type: 'warning',
  })
  if (!ok) return
  try {
    await $fetch(`${apiBase}/wa-gateway/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    pairingCode.value = ''
    fetchWaStatus()
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal memutuskan WhatsApp')
  }
}

let pollInterval: any = null

onMounted(() => {
  fetchWaStatus()
  pollInterval = setInterval(fetchWaStatus, 3000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

const userInitial = computed(() => {
  return authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U'
})

const roleLabel = computed(() => {
  const role = authStore.user?.role
  return role ? (ROLE_LABELS[role] || role) : 'Pengguna'
})

async function saveProfile() {
  if (form.value.new_password) {
    if (!form.value.old_password) {
      errorMsg.value = 'Silakan masukkan kata sandi saat ini untuk mengubah kata sandi.'
      return
    }
    if (form.value.new_password.length < 8) {
      errorMsg.value = 'Kata sandi baru minimal 8 karakter.'
      return
    }
    if (form.value.new_password !== form.value.new_password_confirmation) {
      errorMsg.value = 'Konfirmasi kata sandi baru tidak cocok.'
      return
    }
  }

  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''

  try {
    const res: any = await $fetch(`${authStore.apiBase}/auth/profile`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: form.value,
    })

    if (res.user) {
      authStore.user = res.user
      localStorage.setItem('user', JSON.stringify(res.user))
    }

    form.value.old_password = ''
    form.value.new_password = ''
    form.value.new_password_confirmation = ''
    successMsg.value = res.message || 'Profil berhasil diperbarui!'
  } catch (err: any) {
    errorMsg.value = err?.data?.message || 'Gagal menyimpan pembaruan profil.'
  } finally {
    saving.value = false
  }
}

// SMS OTP Reset Password state & methods
const showSmsModal = ref(false)
const smsStep = ref(1)
const smsLoading = ref(false)
const smsError = ref('')
const smsSuccess = ref('')
const smsMaskedPhone = ref('')
const smsOtpPreview = ref('')
const smsOtpInput = ref('')
const smsCooldown = ref(0)
const smsNewPassword = ref('')
const smsNewPasswordConfirmation = ref('')
let smsCooldownInterval: any = null

function openSmsOtpModal() {
  showSmsModal.value = true
  smsStep.value = 1
  smsError.value = ''
  smsSuccess.value = ''
  smsOtpInput.value = ''
  smsNewPassword.value = ''
  smsNewPasswordConfirmation.value = ''
}

function closeSmsModal() {
  showSmsModal.value = false
}

async function requestSmsOtp() {
  smsLoading.value = true
  smsError.value = ''
  smsSuccess.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/auth/profile/send-sms-otp`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    smsMaskedPhone.value = res.masked_phone || '08******'
    smsOtpPreview.value = res.otp_preview || ''
    smsStep.value = 2
    smsCooldown.value = res.cooldown || 60
    if (smsCooldownInterval) clearInterval(smsCooldownInterval)
    smsCooldownInterval = setInterval(() => {
      if (smsCooldown.value > 0) {
        smsCooldown.value--
      } else {
        clearInterval(smsCooldownInterval)
      }
    }, 1000)
    smsSuccess.value = res.message || 'Kode OTP telah dikirim ke nomor WhatsApp Anda.'
  } catch (e: any) {
    smsError.value = e?.data?.message || 'Gagal mengirim kode OTP ke WhatsApp.'
  } finally {
    smsLoading.value = false
  }
}

async function verifySmsOtp() {
  if (smsOtpInput.value.length !== 6) return
  smsLoading.value = true
  smsError.value = ''
  smsSuccess.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/auth/profile/verify-sms-otp`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { otp: smsOtpInput.value }
    })
    smsStep.value = 3
    smsSuccess.value = res.message || 'Verifikasi WhatsApp berhasil. Silakan masukkan sandi baru.'
  } catch (e: any) {
    smsError.value = e?.data?.message || 'Kode OTP salah atau kedaluwarsa.'
  } finally {
    smsLoading.value = false
  }
}

async function submitNewPasswordViaSms() {
  if (smsNewPassword.value.length < 8) {
    smsError.value = 'Kata sandi minimal 8 karakter.'
    return
  }
  if (smsNewPassword.value !== smsNewPasswordConfirmation.value) {
    smsError.value = 'Konfirmasi kata sandi baru tidak cocok.'
    return
  }

  smsLoading.value = true
  smsError.value = ''
  smsSuccess.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/auth/profile/password-via-sms`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        otp: smsOtpInput.value,
        new_password: smsNewPassword.value,
        new_password_confirmation: smsNewPasswordConfirmation.value
      }
    })
    successMsg.value = res.message || 'Kata sandi berhasil diperbarui via verifikasi WhatsApp.'
    closeSmsModal()
  } catch (e: any) {
    smsError.value = e?.data?.message || 'Gagal mengubah kata sandi via verifikasi WhatsApp.'
  } finally {
    smsLoading.value = false
  }
}
</script>
