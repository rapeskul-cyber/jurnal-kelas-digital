# Web Jurnal Kelas Digital

Aplikasi Web Jurnal Kelas Digital, Presensi, dan Pengelolaan Aktivitas Pembelajaran Siswa dan Guru yang dibangun berdasarkan spesifikasi dan mockup visual presisi tinggi.

---

## 🛠️ Tech Stack

- **Frontend**: **Nuxt 3 (Vue 3)**, Tailwind CSS, Pinia, Lucide Icons, Chart.js / Vue-Chartjs.
- **Backend**: **Laravel 11 (PHP 8.3)**, REST API, Laravel Sanctum Token Authentication, Eloquent ORM.
- **Database**: **SQL (MySQL / MariaDB)**, 14 tabel relasional lengkap dengan migrasi dan seeder data realistis.

---

## 🚀 Cara Menjalankan Project

### 1. Database (MySQL)
Pastikan MySQL sudah aktif (misal lewat XAMPP di port `3306`), lalu buat database:
```sql
CREATE DATABASE jurnal_kelas_digital;
```

### 2. Backend (Laravel)
Masuk ke direktori `backend`:
```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve --port=8000
```
Backend API berjalan di: `http://localhost:8000`

### 3. Frontend (Nuxt 3)
Masuk ke direktori `frontend`:
```bash
cd frontend
npm install
npm run dev
```
Frontend Web App berjalan di: `http://localhost:3000`

---

## 👥 Akun Uji Coba & Hak Akses (Role)

Semua akun dev menggunakan kata sandi default: **`password123`** (atau gunakan tombol **Quick Login** 1-klik di halaman login):

| Peran | Nama | Email | Hak Akses Utama |
|---|---|---|---|
| **Siswa** | Rina Safitri | `siswa@sekolah.sch.id` | Dashboard Siswa, Riwayat Kehadiran (Donut & Weekly Chart), Jurnal Pelajaran & Unduh Materi |
| **Sekretaris** | Ahmad Fadillah | `sekretaris@sekolah.sch.id` | Input Presensi Harian (Segmented Buttons), Kunci Sesi (Verifikasi PIN 1234), Input Jurnal Kelas Harian, Pengajuan Surat Izin (Tanda Tangan Digital) |
| **Guru Pengajar** | Budi Santoso, S.Pd | `pengajar@sekolah.sch.id` | Konfirmasi & Verifikasi Jurnal Kelas Masuk, Publikasi Rencana Belajar / Modul / Tugas |
| **Guru Pembimbing** | Dra. Siti Aminah, M.Pd | `pembimbing@sekolah.sch.id` | Persetujuan / Penolakan Surat Izin Siswa (Split View), Rekap Semester & Ekspor XLSX/PDF, Manajemen Pengguna & Reset Sandi, Matriks RBAC, Audit Log Trail |

---

## 📑 Struktur Halaman & Fitur Frontend Nuxt

1. **Autentikasi**:
   - `/login`: Form Login, Show/Hide Password, Tombol Quick Login 4 peran.
   - `/forgot-password`: 3-Step Wizard Reset Password (Email -> OTP -> Buat Sandi Baru -> Sukses).
2. **Dashboard**:
   - `/dashboard`: Tampilan adaptif otomatis mendeteksi role pengguna yang login.
3. **Presensi & Rekapitulasi**:
   - `/presensi`: Formulir presensi kelas lengkap dengan tombol Hadir/Sakit/Izin/Alpha, tombol Hadirkan Semua, validasi status, dan modal kunci sesi dengan PIN.
   - `/riwayat-kehadiran`: Riwayat presensi personal siswa dengan Donut Chart persentase kehadiran dan bar chart mingguan.
   - `/rekap-harian`: Laporan harian presensi per kelas dengan KPI cards, pencarian, dan tombol ekspor.
   - `/rekap-semester`: Rekapitulasi presensi kumulatif per semester, persentase kehadiran siswa, predikat, cetak PDF dan Excel.
4. **Jurnal Pembelajaran**:
   - `/jurnal-kelas`: Input aktivitas kegiatan belajar mengajar harian oleh sekretaris.
   - `/jurnal-materi`: Panel guru pengajar untuk konfirmasi jurnal yang masuk dan publikasi materi/tugas baru.
   - `/jurnal-pelajaran`: Halaman siswa untuk membaca topik bahasan, materi, dan instruksi tugas.
   - `/laporan-jurnal`: Log komprehensif seluruh aktivitas jurnal kelas dan status verifikasinya.
5. **Surat Izin**:
   - `/surat-izin`: Pengajuan izin tidak masuk dengan pad tanda tangan digital HTML5 canvas.
   - `/persetujuan-izin`: Panel guru pembimbing untuk meninjau, menyetujui, atau menolak izin dengan catatan verifikasi.
   - `/laporan-izin`: Arsip seluruh permohonan izin dan ekspor data XLSX.
6. **Administrasi & Sistem**:
   - `/manajemen-user`: Tab Daftar Pengguna (tambah user, switch aktif/nonaktif, reset sandi) dan Tab Matriks Hak Akses (RBAC).
   - `/notifikasi`: Pusat notifikasi dengan filter (Jurnal, Surat Izin, Belum Dibaca) dan Log Audit Trail aktivitas sistem.
