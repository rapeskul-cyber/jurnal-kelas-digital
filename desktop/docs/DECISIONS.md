# Catatan Keputusan Arsitektur & Desain (docs/DECISIONS.md)

Dokumen ini mencatat keputusan teknis dan implementasi untuk proyek **Web Jurnal Kelas Digital**.

## 1. Database & ORM
- **Database**: PostgreSQL lokal (`postgresql://postgres:root@localhost:5432/jurnal_kelas_digital?schema=public`).
- **ORM**: Prisma Client dengan migrasi via `prisma migrate dev`.

## 2. Autentikasi & Sesi
- **Metode**: Cookie `httpOnly`, tabel `Session` menyimpan token acak yang di-hash SHA-256.
- **Password**: Hashing menggunakan `bcryptjs` / `argon2`.
- **OTP**: Interface `OtpSender` dengan implementasi aktif `ConsoleOtpSender` untuk development, dan `FonnteOtpSender` (stub dengan TODO dan pembacaan `FONNTE_TOKEN`).

## 3. Desain UI & Token
- **Framework UI**: Next.js App Router + Tailwind CSS + Lucide Icons + shadcn/ui primitives.
- **Warna Utama**: Primary Red `#C62828`, Latar `#FAFAFA`, Kartu Putih `#FFFFFF`, Border `#E5E7EB`.
- **Status Presensi**: Hadir `#16A34A` (Hijau), Sakit `#EA580C` (Oranye), Izin `#2563EB` (Biru), Alpha `#DC2626` (Merah).
- **Format Tanggal**: Bahasa Indonesia (`id-ID`), Timezone `Asia/Jakarta (WIB)`.

## 4. Arsitektur Kode "Ponytail"
- Konfigurasi peran terpusat di `src/config/access.ts` (menu, izin, route guard, kartu dashboard).
- Konstanta terpusat di `src/config/constants.ts`.
- Validasi terpusat di `src/lib/schemas/` dengan Zod.
- Reusable pages parameterised by role (`MaterialListPage`, `ClassJournalReportPage`, `DailyRecapPage`, `SemesterRecapPage`, `NotificationsPage`).
