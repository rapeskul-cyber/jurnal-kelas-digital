# 🏫 Jurnal Kelas Digital

![License](https://img.shields.io/badge/License-MIT-blue.svg)
![Laravel](https://img.shields.io/badge/Backend-Laravel_11-FF2D20?logo=laravel)
![Nuxt](https://img.shields.io/badge/Frontend-Nuxt_3_(Vue_3)-00DC82?logo=nuxtdotjs)
![TailwindCSS](https://img.shields.io/badge/Styling-Tailwind_CSS-38B2AC?logo=tailwind-css)
![WhatsApp](https://img.shields.io/badge/Gateway-Baileys_WA-25D366?logo=whatsapp)
![Status](https://img.shields.io/badge/Status-Industry_Ready-success)

Sistem Informasi Manajemen Kegiatan Belajar Mengajar (KBM), Presensi Digital Terpadu, Jurnal Guru, Perizinan Siswa, dan Notifikasi WhatsApp Gateway Mandiri berbasis Web & Mobile PWA untuk institusi pendidikan kejuruan modern.

---

## 📌 Ringkasan Proyek

**Jurnal Kelas Digital** dirancang untuk mendigitalkan seluruh administrasi ruang kelas secara real-time dan terintegrasi. Sistem mengakomodasi kebutuhan presensi per jam pelajaran, pencatatan capaian materi pembelajaran harian, pengajuan izin/sakit siswa, rekapitulasi kehadiran berkala, serta pengiriman pesan otomatis ke nomor WhatsApp orang tua/wali murid saat kehadiran tercatat.

Repositori ini menerapkan arsitektur **Dual-Platform Client** yang berbagi skema database dan protokol otentikasi RESTful API yang sama:
1. ****: Dasbor komprehensif bagi Administrator, Manajemen Sekolah, dan Guru untuk rekapitulasi data, input jurnal harian, modul ajar, dan pengaturan sistem.
2. ****: Antarmuka ringkas berorientasi *mobile-first* (Card & Bottom Navigation bar) yang dioptimalkan untuk perangkat ponsel pintar guru di kelas dan siswa.

---

## ✨ Fitur Utama

### 1. 📱 Integrasi WhatsApp Gateway Mandiri (Baileys Engine)
* **Pairing QR Instan:** Cukup scan QR code dari antarmuka web untuk menghubungkan akun WhatsApp gateway tanpa ketergantungan API berbayar pihak ketiga.
* **Auto-Polling & Live Status:** Deteksi status koneksi real-time dengan auto-reconnect cerdas.
* **Notifikasi Presensi Otomatis:** Mengirimkan pesan konfirmasi kehadiran atau pemberitahuan ketidakhadiran (Alpha/Izin/Sakit) secara otomatis ke kontak siswa dan orang tua.

### 2. 📝 Jurnal Mengajar & Manajemen Modul Ajar (KBM)
* Input materi pembelajaran harian, tujuan pembelajaran, dan indikator pencapaian kompetensi per rombel.
* Pengunggahan dan pratinjau (*preview*) berkas modul ajar (PDF) langsung di browser, disertai tombol unduh dokumen terverifikasi.

### 3. 🕒 Presensi Real-Time & Rekapitulasi Multi-Periode
* Presensi fleksibel per jam tatap muka dengan status: **Hadir (H)**, **Sakit (S)**, **Izin (I)**, dan **Alpa (A)**.
* Pengajuan izin/sakit mandiri oleh siswa dilengkapi form unggah surat dokter / foto bukti fisik.
* Persetujuan (*approval*) izin secara berjenjang oleh Guru Piket atau Wali Kelas.
* Rekapitulasi otomatis dalam persentase bulanan/semester yang siap diekspor ke format pelaporan berkala.

### 4. 🏢 Master Data Terstruktur & Multi-Jurusan
* **18 Rombongan Belajar Reguler:**
  * **RPL (Rekayasa Perangkat Lunak):** Tingkat X, XI, XII (Kelas 1 & 2) — *Identitas Warna Merah*
  * **DKV (Desain Komunikasi Visual):** Tingkat X, XI, XII (Kelas 1 & 2) — *Identitas Warna Hijau*
  * **Animasi:** Tingkat X, XI, XII (Kelas 1 & 2) — *Identitas Warna Kuning*
* **1 Rombongan Belajar Khusus (Kelas Industri):**
  * Terisolasi penuh dengan kuota 15 siswa dan dibina langsung oleh Guru Pembimbing Industri.
* **26 Tenaga Pendidik Terdaftar** dengan jadwal mata pelajaran kejuruan dan umum yang terstruktur.

### 5. 🛡️ Role-Based Access Control (RBAC) Ketat
* **Superadmin:** Konfigurasi sekolah, manajemen tahun ajaran/semester, kontrol server, dan audit log.
* **Guru / Pembimbing:** Input jurnal KBM, pengisian presensi siswa, verifikasi perizinan, dan modul ajar.
* **Siswa:** Akses riwayat presensi mandiri, modul ajar kelas, jadwal harian, dan pengajuan izin/sakit.
* **Petugas Piket:** Monitoring gerbang absensi global dan verifikasi surat dispensasi keluar kelas.

### 6. 📊 Manajemen Peserta Didik & Import Massal
* Pengelolaan data siswa per kelas dengan dukungan import massal berbasis berkas spreadsheet (CSV / Excel).

---

## 📂 Struktur Repositori



---

## 🛠️ Tech Stack & Spesifikasi

| Lapisan | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Frontend Framework** | Nuxt 3 (Vue 3 Composition API) | Universal SSR/SPA dengan Vite bundler |
| **State Management** | Pinia | Global reactive store untuk sesi user & master data |
| **Styling & Icons** | Tailwind CSS + Lucide Icons | Antarmuka adaptif, clean typography, dan icon SVG |
| **Backend Framework** | Laravel 11 (PHP 8.2+) | Arsitektur MVC REST API dengan Eloquent ORM |
| **Authentication** | Laravel Sanctum | Token-based auth aman dengan expiry & role guarding |
| **Database Engine** | SQLite / MySQL / MariaDB | Penyimpanan relasional terindeks ACID compliance |
| **WhatsApp Engine** | Node.js + @whiskeysockets/baileys | Gateway pesan WhatsApp multi-device mandiri |

---

## 🚀 Panduan Instalasi & Menjalankan

### Prasyarat Sistem
* **PHP:** >= 8.2 (ekstensi ,  atau , , )
* **Node.js:** >= 18.x
* **Composer:** >= 2.5
* **NPM / Bun / Yarn**

---

### Langkah 1: Setup Backend API (Laravel 11)

Navigasikan ke direktori backend:


> **Info:** Backend API aktif melayani request di .

---

### Langkah 2: Setup Frontend Web (Nuxt 3)

#### Untuk Versi Desktop:

up to date, audited 138 packages in 5s

19 packages are looking for funding
  run `npm fund` for details

12 high severity vulnerabilities

To address issues that do not require attention, run:
  npm audit fix

To address all issues (including breaking changes), run:
  npm audit fix --force

Run `npm audit` for details.
> Akses melalui browser: 

#### Untuk Versi Mobile:

up to date, audited 138 packages in 1s

19 packages are looking for funding
  run `npm fund` for details

12 high severity vulnerabilities

To address issues that do not require attention, run:
  npm audit fix

To address all issues (including breaking changes), run:
  npm audit fix --force

Run `npm audit` for details.
> Akses melalui browser:  (dianjurkan membuka *Device Mode / Inspect Mobile View* di browser).

---

## 🔑 Kredensial Akun Pengujian (Demo Data)

Setelah menjalankan seeder default (), akun pengujian berikut siap digunakan:

| Peran (Role) | Identitas Pengguna | Email / NIP | Password Default | Akses Menu |
| :--- | :--- | :--- | :--- | :--- |
| **Superadmin** | Administrator Sistem |  |  | Konfigurasi global, User, WhatsApp Gateway, Pengaturan |
| **Pembimbing Industri** | Guru Pembimbing (Kelas Industri) |  |  | Jurnal KBM, Presensi Kelas Industri, Modul Ajar |
| **Guru Reguler** | Guru Pengampu Kejuruan |  |  | Jurnal KBM, Presensi Kelas Reguler, Modul Ajar |
| **Siswa** | Siswa Perwakilan Kelas |  |  | Dashboard Siswa, Riwayat Kehadiran, Form Pengajuan Izin |

---

## 🛡️ Keamanan & Integritas Data
* **Sanitasi Kredensial:** Seluruh berkas sensitif (, database credentials, log file, token session) dilindungi oleh  dan tidak terlacak ke VCS.
* **Prinsip Least Privilege:** Endpoint REST API backend diproteksi middleware Sanctum dengan pengecekan peran ketat di level controller.
* **Audit Trail:** Modul audit log mencatat setiap interaksi pengubahan data presensi dan status persetujuan surat izin secara kronologis.

---

## 👨‍💻 Hak Cipta & Lisensi
Dikembangkan untuk keperluan operasional akademik dan sistem informasi manajemen kelas digital.

Didistribusikan di bawah lisensi open-source **MIT License**.
