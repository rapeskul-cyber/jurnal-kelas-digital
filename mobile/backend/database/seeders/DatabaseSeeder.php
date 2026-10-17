<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Term;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Term
        $term = Term::create([
            'name' => '2025/2026 - Ganjil',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'effective_days' => 108,
            'is_active' => true,
        ]);

        // 2. Classes
        $class1 = SchoolClass::create(['name' => 'XI RPL 1']);
        $class2 = SchoolClass::create(['name' => 'XI RPL 2']);
        $class3 = SchoolClass::create(['name' => 'XII RPL 1']);
        $class4 = SchoolClass::create(['name' => 'XII RPL 2']);

        // 3. Guru Pembimbing (Advisor)
        $pembimbing = User::create([
            'name' => 'Siti Aminah, M.Pd',
            'email' => 'siti.aminah@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567801',
            'nip_nis' => '19890314002',
            'role' => 'GURU_PEMBIMBING',
            'is_active' => true,
        ]);

        // 4. Guru Pengajar (Teacher)
        $pengajar = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi.santoso@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567802',
            'nip_nis' => '19840212001',
            'role' => 'GURU_PENGAJAR',
            'is_active' => true,
        ]);

        // Set advisor for class 1 & 2
        $class1->update(['advisor_id' => $pembimbing->id]);
        $class2->update(['advisor_id' => $pembimbing->id]);

        // Teacher Class Relation
        $pengajar->taughtClasses()->attach([$class1->id, $class2->id, $class3->id, $class4->id]);

        // 5. Sekretaris Kelas
        $sekretaris = User::create([
            'name' => 'Rina Safitri',
            'email' => 'rina.safitri@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6285812343414',
            'nip_nis' => '102401',
            'role' => 'SEKRETARIS',
            'class_id' => $class1->id,
            'is_active' => true,
        ]);
        $class1->update(['secretary_id' => $sekretaris->id]);

        // 6. Siswa Biasa (Ahmad Fadillah)
        $siswaFadillah = User::create([
            'name' => 'Ahmad Fadillah',
            'email' => 'ahmad.fadillah@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567804',
            'nip_nis' => '102402',
            'role' => 'SISWA',
            'class_id' => $class1->id,
            'is_active' => true,
        ]);

        // 7. Siswa Lainnya di XI RPL 1 (Mockup names)
        $studentsData = [
            ['name' => 'Annisa Rahmawati', 'email' => 'annisa.rahmawati@gmail.com', 'nis' => '102403'],
            ['name' => 'Bambang Wijaya', 'email' => 'bambang.wijaya@gmail.com', 'nis' => '102404'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@gmail.com', 'nis' => '102405'],
            ['name' => 'Dinda Maharani', 'email' => 'dinda.maharani@gmail.com', 'nis' => '102406'],
            ['name' => 'Farhan Maulana', 'email' => 'farhan.maulana@gmail.com', 'nis' => '102407'],
            ['name' => 'Nabila Azzahra', 'email' => 'nabila.azzahra@gmail.com', 'nis' => '102408'],
            ['name' => 'Salsabila Putri', 'email' => 'salsabila.putri@gmail.com', 'nis' => '102409'],
            ['name' => 'Rizky Ramadhan', 'email' => 'rizky.ramadhan@gmail.com', 'nis' => '102410'],
            ['name' => 'Alif Pratama', 'email' => 'alif.pratama@gmail.com', 'nis' => '102411'],
            ['name' => 'Hanif Akbar', 'email' => 'hanif.akbar@gmail.com', 'nis' => '102412'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@gmail.com', 'nis' => '102413'],
            ['name' => 'Sarah Sinta', 'email' => 'sarah.sinta@gmail.com', 'nis' => '102414'],
            ['name' => 'Nadia Omara', 'email' => 'nadia.omara@gmail.com', 'nis' => '102415'],
            ['name' => 'Andi Susanto', 'email' => 'andi.susanto@gmail.com', 'nis' => '102416'],
            ['name' => 'Aditya Pratama', 'email' => 'aditya.pratama@gmail.com', 'nis' => '102417'],
            ['name' => 'Dimas Anggara', 'email' => 'dimas.anggara@gmail.com', 'nis' => '102418'],
            ['name' => 'Eka Prasetya', 'email' => 'eka.prasetya@gmail.com', 'nis' => '102419'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar.nugraha@gmail.com', 'nis' => '102420'],
            ['name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan@gmail.com', 'nis' => '102421'],
            ['name' => 'Hafiz Alamsyah', 'email' => 'hafiz.alamsyah@gmail.com', 'nis' => '102422'],
            ['name' => 'Indah Permata', 'email' => 'indah.permata@gmail.com', 'nis' => '102423'],
            ['name' => 'Joko Triyono', 'email' => 'joko.triyono@gmail.com', 'nis' => '102424'],
            ['name' => 'Kirana Dewi', 'email' => 'kirana.dewi@gmail.com', 'nis' => '102425'],
            ['name' => 'Luthfi Hakim', 'email' => 'luthfi.hakim@gmail.com', 'nis' => '102426'],
            ['name' => 'Mega Utami', 'email' => 'mega.utami@gmail.com', 'nis' => '102427'],
            ['name' => 'Nurul Hidayah', 'email' => 'nurul.hidayah@gmail.com', 'nis' => '102428'],
            ['name' => 'Oki Setiawan', 'email' => 'oki.setiawan@gmail.com', 'nis' => '102429'],
            ['name' => 'Putri Ayu', 'email' => 'putri.ayu@gmail.com', 'nis' => '102430'],
            ['name' => 'Qori Santika', 'email' => 'qori.santika@gmail.com', 'nis' => '102431'],
            ['name' => 'Reza Fahlevi', 'email' => 'reza.fahlevi@gmail.com', 'nis' => '102432'],
            ['name' => 'Syahrul Gunawan', 'email' => 'syahrul.gunawan@gmail.com', 'nis' => '102433'],
            ['name' => 'Taufik Hidayat', 'email' => 'taufik.hidayat@gmail.com', 'nis' => '102434'],
            ['name' => 'Wahyu Pratama', 'email' => 'wahyu.pratama@gmail.com', 'nis' => '102435'],
            ['name' => 'Zahra Amelia', 'email' => 'zahra.amelia@gmail.com', 'nis' => '102436'],
        ];

        $allStudents = [$sekretaris, $siswaFadillah];
        foreach ($studentsData as $idx => $s) {
            $student = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password123'),
                'phone' => '62858' . str_pad($idx + 30, 8, '0', STR_PAD_LEFT),
                'nip_nis' => $s['nis'],
                'role' => 'SISWA',
                'class_id' => $class1->id,
                'is_active' => true,
            ]);
            $allStudents[] = $student;
        }

        // 8. Subjects
        $subPW = Subject::create(['name' => 'Pemrograman Web', 'code' => 'PW-01']);
        $subBD = Subject::create(['name' => 'Basis Data', 'code' => 'BD-01']);
        $subPKL = Subject::create(['name' => 'Praktek Kerja Lapangan', 'code' => 'PKL-01']);
        $subKWU = Subject::create(['name' => 'Kewirausahaan', 'code' => 'KWU-01']);

        // 9. Schedules
        $sch1 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subPW->id,
            'teacher_id' => $pengajar->id,
            'day' => 'Kamis',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Lab. 1',
        ]);
        $sch2 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subBD->id,
            'teacher_id' => $pengajar->id,
            'day' => 'Kamis',
            'start_time' => '09:15',
            'end_time' => '11:45',
            'room' => 'Lab. 1',
        ]);
        $sch3 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subPKL->id,
            'teacher_id' => $pengajar->id,
            'day' => 'Jumat',
            'start_time' => '10:30',
            'end_time' => '12:00',
            'room' => 'Lab. 1',
        ]);
        $sch4 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subKWU->id,
            'teacher_id' => $pembimbing->id,
            'day' => 'Jumat',
            'start_time' => '13:00',
            'end_time' => '15:30',
            'room' => 'Lab. 1',
        ]);

        // 10. Attendance Sessions & Records (Today and Past Days)
        $today = Carbon::today();
        $sessionToday = AttendanceSession::create([
            'class_id' => $class1->id,
            'date' => $today->toDateString(),
            'status' => 'LOCKED',
            'locked_by_id' => $sekretaris->id,
            'locked_at' => $today->copy()->setTime(8, 0),
            'verification_status' => 'TERVERIFIKASI',
            'school_hours' => '07:30 - 15:30',
            'room' => 'Lab. 1',
        ]);

        foreach ($allStudents as $std) {
            $status = 'HADIR';
            $note = null;
            if ($std->nip_nis === '102403') { // Annisa Rahmawati
                $status = 'SAKIT';
                $note = 'Demam sejak pagi, surat dokter terlampir';
            } elseif ($std->nip_nis === '102409') { // Salsabila Putri
                $status = 'IZIN';
                $note = 'Keperluan keluarga di luar kota';
            } elseif ($std->nip_nis === '102410') { // Rizky Ramadhan
                $status = 'ALPHA';
                $note = 'Belum ada keterangan';
            }

            AttendanceRecord::create([
                'session_id' => $sessionToday->id,
                'student_id' => $std->id,
                'status' => $status,
                'note' => $note,
            ]);
        }

        // Add 3 past sessions to provide realistic charts & monthly history
        for ($i = 1; $i <= 3; $i++) {
            $pastDate = $today->copy()->subDays($i * 2);
            $pastSession = AttendanceSession::create([
                'class_id' => $class1->id,
                'date' => $pastDate->toDateString(),
                'status' => 'LOCKED',
                'locked_by_id' => $sekretaris->id,
                'locked_at' => $pastDate->copy()->setTime(8, 15),
                'verification_status' => 'TERVERIFIKASI',
                'school_hours' => '07:30 - 15:30',
                'room' => 'Lab. 1',
            ]);

            foreach ($allStudents as $std) {
                AttendanceRecord::create([
                    'session_id' => $pastSession->id,
                    'student_id' => $std->id,
                    'status' => 'HADIR',
                    'note' => 'Presensi terverifikasi',
                ]);
            }
        }

        // 11. Class Journals (Jurnal Kelas)
        ClassJournal::create([
            'class_id' => $class1->id,
            'schedule_id' => $sch1->id,
            'date' => $today->toDateString(),
            'activity' => 'Optimasi Query relasional dan implementasi routing SPA di web sekolah.',
            'notes' => 'Seluruh siswa mempraktikkan konfigurasi API.',
            'verification_status' => 'TERVERIFIKASI',
            'created_by_id' => $sekretaris->id,
            'confirmed_by_id' => $pengajar->id,
            'confirmed_at' => $today->copy()->setTime(9, 5),
        ]);

        ClassJournal::create([
            'class_id' => $class1->id,
            'schedule_id' => $sch2->id,
            'date' => $today->toDateString(),
            'activity' => 'Rancangan ERD lanjutan dan perbaikan skema database normalisasi 3NF.',
            'notes' => 'Menunggu verifikasi guru pengajar.',
            'verification_status' => 'PENDING',
            'created_by_id' => $sekretaris->id,
        ]);

        // 12. Material Journals (Jurnal Materi Guru)
        MaterialJournal::create([
            'teacher_id' => $pengajar->id,
            'class_id' => $class1->id,
            'subject_id' => $subPW->id,
            'schedule_id' => $sch1->id,
            'date' => $today->toDateString(),
            'topic' => 'Optimasi Query relasional dan teknik routing SPA.',
            'plan' => 'Membahas arsitektur komponen, state management, dan fetch data API.',
            'assignment' => 'Siswa ditugaskan membuat rancangan CRUD mandiri.',
        ]);

        MaterialJournal::create([
            'teacher_id' => $pengajar->id,
            'class_id' => $class1->id,
            'subject_id' => $subBD->id,
            'schedule_id' => $sch2->id,
            'date' => $today->toDateString(),
            'topic' => 'Rancangan ERD lanjutan dan perbaikan Schema.',
            'plan' => 'Normalisasi tabel hingga 3NF dan pembuatan index relasi.',
            'assignment' => 'Latihan migrasi database dan pengujian relasi foreign key.',
        ]);

        MaterialJournal::create([
            'teacher_id' => $pengajar->id,
            'class_id' => $class1->id,
            'subject_id' => $subPKL->id,
            'schedule_id' => $sch3->id,
            'date' => $today->copy()->subDays(1)->toDateString(),
            'topic' => 'Briefing persiapan pendelegasian magang di Industri.',
            'plan' => 'Penjelasan SOP kerja dan etika komunikasi profesional.',
            'assignment' => 'Penyusunan resume profil portofolio siswa.',
        ]);

        // 13. Leave Requests (Surat Izin)
        $leaveAnnisa = LeaveRequest::create([
            'student_id' => $allStudents[2]->id, // Annisa Rahmawati
            'submitted_by_id' => $sekretaris->id,
            'type' => 'SAKIT',
            'start_date' => $today->toDateString(),
            'end_date' => $today->copy()->addDays(2)->toDateString(),
            'recipient' => 'Guru Pembimbing Sekolah / Wali Kelas',
            'reason' => 'Mengalami gejala demam tinggi dan flu berat sejak semalam, disarankan istirahat oleh dokter klinik keluarga demi pemulihan optimal.',
            'status' => 'PENDING',
        ]);

        $leaveFadillah = LeaveRequest::create([
            'student_id' => $siswaFadillah->id,
            'submitted_by_id' => $sekretaris->id,
            'type' => 'SAKIT',
            'start_date' => $today->copy()->subDays(4)->toDateString(),
            'end_date' => $today->copy()->subDays(3)->toDateString(),
            'recipient' => 'Guru Pembimbing Sekolah / Wali Kelas',
            'reason' => 'Demam dan flu, istirahat di rumah.',
            'status' => 'APPROVED',
            'reviewer_id' => $pembimbing->id,
            'review_note' => 'Disetujui. Semoga lekas sembuh.',
            'reviewed_at' => $today->copy()->subDays(4)->setTime(10, 0),
        ]);

        // 14. Notifications
        Notification::create([
            'user_id' => $pembimbing->id,
            'type' => 'SURAT_IZIN',
            'title' => 'Permohonan Surat Izin Baru',
            'body' => 'Sekretaris Rina Safitri mengajukan surat izin sakit untuk Annisa Rahmawati (XI RPL 1).',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $pengajar->id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Kelas Menunggu Konfirmasi',
            'body' => 'Jurnal kelas mata pelajaran Basis Data (XI RPL 1) menunggu konfirmasi dari Anda.',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $siswaFadillah->id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin Disetujui',
            'body' => 'Surat izin sakit Anda telah disetujui oleh Guru Pembimbing Siti Aminah, M.Pd.',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $siswaFadillah->id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Materi Telah Dikirim',
            'body' => 'Guru Pemateri telah mengirimkan Materi Baru untuk Pemrograman Web.',
            'read_at' => null,
        ]);

        // 15. Audit Logs
        AuditLog::create([
            'user_id' => $sekretaris->id,
            'action' => 'Mengirimkan data presensi & draf jurnal harian XI RPL 1',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $pengajar->id,
            'action' => 'Mengisi Jurnal Materi Pemrograman Web',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $sekretaris->id,
            'action' => 'Mengajukan permohonan surat izin sakit Annisa Rahmawati',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
