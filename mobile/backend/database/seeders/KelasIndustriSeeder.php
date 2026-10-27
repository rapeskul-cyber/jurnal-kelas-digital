<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasIndustriSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // Bersihkan data lama agar skala murni khusus Kelas Industri
            DB::statement('PRAGMA foreign_keys = OFF;');
            AttendanceRecord::truncate();
            AttendanceSession::truncate();
            ClassJournal::truncate();
            MaterialJournal::truncate();
            LeaveRequest::truncate();
            Schedule::truncate();
            DB::table('teacher_classes')->truncate();
            Subject::truncate();
            SchoolClass::truncate();
            User::truncate();
            Notification::truncate();
            AuditLog::truncate();
            DB::statement('PRAGMA foreign_keys = ON;');

            // 1. SUPERADMINISTRATOR
            $superadmin = User::create([
                'id' => 1,
                'name' => 'Super Administrator',
                'email' => 'superadmin@sekolah.sch.id',
                'password' => Hash::make('password123'),
                'phone' => '081299990001',
                'nip_nis' => '198001012005011001',
                'role' => 'SUPERADMIN',
                'specialization' => 'Kepala Lab & Administrator Pusat',
                'is_active' => true,
            ]);

            // 2. BU FADHILAH - Wali Kelas Industri (Guru Pembimbing)
            $fadhilah = User::create([
                'id' => 2,
                'name' => 'Nurhayatul Fadilla, S.Pd',
                'email' => 'fadhilah@sekolah.sch.id',
                'password' => Hash::make('password123'),
                'phone' => '081288990011',
                'nip_nis' => '198706152011012001',
                'role' => 'GURU_PEMBIMBING',
                'specialization' => 'Wali Kelas Industri & Konsentrasi RPL',
                'is_active' => true,
            ]);

            // 3. PAK BAGAS - Guru Pengajar Kelas Industri
            $bagas = User::create([
                'id' => 3,
                'name' => 'Bagas Pratama, S.Kom',
                'email' => 'bagas@sekolah.sch.id',
                'password' => Hash::make('password123'),
                'phone' => '081288990022',
                'nip_nis' => '199003202015021002',
                'role' => 'GURU_PENGAJAR',
                'specialization' => 'Pemrograman Web, Mobile & IoT Industri',
                'is_active' => true,
            ]);

            // 4. KELAS INDUSTRI (ID: 1)
            $kelasIndustri = SchoolClass::create([
                'id' => 1,
                'name' => 'Kelas Industri',
                'major' => 'RPL',
                'advisor_id' => $fadhilah->id,
            ]);

            // 5. 15 SISWA KELAS INDUSTRI (7 Asal XI RPL 1, 8 Asal XI RPL 2)
            $studentsData = [
                // Dari XI RPL 1
                ['id' => 10, 'name' => 'Raffi Rasyid Rizqi', 'origin' => 'XI RPL 1', 'nis' => '102401', 'is_sec' => true, 'email' => 'raffi@sekolah.sch.id'],
                ['id' => 11, 'name' => 'Faishal Hisyam', 'origin' => 'XI RPL 1', 'nis' => '102402', 'is_sec' => false, 'email' => 'faishal@sekolah.sch.id'],
                ['id' => 12, 'name' => 'Jimmy Fadhilah', 'origin' => 'XI RPL 1', 'nis' => '102403', 'is_sec' => false, 'email' => 'jimmy@sekolah.sch.id'],
                ['id' => 13, 'name' => 'Vidhie Musyafa', 'origin' => 'XI RPL 1', 'nis' => '102404', 'is_sec' => false, 'email' => 'vidhie@sekolah.sch.id'],
                ['id' => 14, 'name' => 'Dira', 'origin' => 'XI RPL 1', 'nis' => '102405', 'is_sec' => false, 'email' => 'dira@sekolah.sch.id'],
                ['id' => 15, 'name' => 'Assyifa', 'origin' => 'XI RPL 1', 'nis' => '102406', 'is_sec' => false, 'email' => 'assyifa@sekolah.sch.id'],
                ['id' => 16, 'name' => 'Muhammad Girsan A', 'origin' => 'XI RPL 1', 'nis' => '102407', 'is_sec' => false, 'email' => 'girsan@sekolah.sch.id'],
                // Dari XI RPL 2
                ['id' => 17, 'name' => 'M Favian Gunawan', 'origin' => 'XI RPL 2', 'nis' => '102408', 'is_sec' => false, 'email' => 'favian@sekolah.sch.id'],
                ['id' => 18, 'name' => 'Rifqi', 'origin' => 'XI RPL 2', 'nis' => '102409', 'is_sec' => false, 'email' => 'rifqi@sekolah.sch.id'],
                ['id' => 19, 'name' => 'Rafa', 'origin' => 'XI RPL 2', 'nis' => '102410', 'is_sec' => false, 'email' => 'rafa@sekolah.sch.id'],
                ['id' => 20, 'name' => 'Akmal', 'origin' => 'XI RPL 2', 'nis' => '102411', 'is_sec' => false, 'email' => 'akmal@sekolah.sch.id'],
                ['id' => 21, 'name' => 'Taqiy', 'origin' => 'XI RPL 2', 'nis' => '102412', 'is_sec' => false, 'email' => 'taqiy@sekolah.sch.id'],
                ['id' => 22, 'name' => 'Adit', 'origin' => 'XI RPL 2', 'nis' => '102413', 'is_sec' => false, 'email' => 'adit@sekolah.sch.id'],
                ['id' => 23, 'name' => 'Nadila', 'origin' => 'XI RPL 2', 'nis' => '102414', 'is_sec' => false, 'email' => 'nadila@sekolah.sch.id'],
                ['id' => 24, 'name' => 'Safira', 'origin' => 'XI RPL 2', 'nis' => '102415', 'is_sec' => false, 'email' => 'safira@sekolah.sch.id'],
            ];

            $createdStudents = [];
            foreach ($studentsData as $idx => $st) {
                $role = $st['is_sec'] ? 'SEKRETARIS' : 'SISWA';
                $student = User::create([
                    'id' => $st['id'],
                    'name' => $st['name'],
                    'email' => $st['email'],
                    'password' => Hash::make('password123'),
                    'phone' => '0857' . str_pad($idx + 10, 8, '0', STR_PAD_LEFT),
                    'nip_nis' => $st['nis'],
                    'role' => $role,
                    'specialization' => "Asal {$st['origin']}",
                    'class_id' => $kelasIndustri->id,
                    'is_active' => true,
                ]);

                if ($st['is_sec']) {
                    $kelasIndustri->secretary_id = $student->id;
                    $kelasIndustri->save();
                }

                $createdStudents[] = $student;
            }

            // 6. MATA PELAJARAN KELAS INDUSTRI
            $subWeb = Subject::create([
                'id' => 1,
                'code' => 'IND-WEB',
                'name' => 'Pemrograman Web & Mobile Industri',
                'major' => 'RPL',
                'teacher_id' => $bagas->id,
            ]);

            $subIoT = Subject::create([
                'id' => 2,
                'code' => 'IND-IOT',
                'name' => 'Internet of Things (IoT) & Cloud Architecture',
                'major' => 'RPL',
                'teacher_id' => $bagas->id,
            ]);

            $subDB = Subject::create([
                'id' => 3,
                'code' => 'IND-DB',
                'name' => 'Rekayasa Basis Data & API Gateway',
                'major' => 'RPL',
                'teacher_id' => $fadhilah->id,
            ]);

            $subPKK = Subject::create([
                'id' => 4,
                'code' => 'IND-PKK',
                'name' => 'Proyek Kreatif & Kewirausahaan Industri (Startup Lab)',
                'major' => 'RPL',
                'teacher_id' => $bagas->id,
            ]);

            // Assign Guru ke Rombel
            $bagas->taughtClasses()->sync([$kelasIndustri->id]);
            $fadhilah->taughtClasses()->sync([$kelasIndustri->id]);

            // 7. JADWAL KBM KELAS INDUSTRI
            $schedules = [
                ['day' => 'Senin', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Senin', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subDB->id, 'teacher_id' => $fadhilah->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Selasa', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Selasa', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subPKK->id, 'teacher_id' => $bagas->id, 'room' => 'Studio Startup 71'],
                ['day' => 'Rabu', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subIoT->id, 'teacher_id' => $bagas->id, 'room' => 'Lab IoT & Embedded'],
                ['day' => 'Rabu', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subDB->id, 'teacher_id' => $fadhilah->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Kamis', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subPKK->id, 'teacher_id' => $bagas->id, 'room' => 'Studio Startup 71'],
                ['day' => 'Kamis', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Jumat', 'start_time' => '07:30', 'end_time' => '11:00', 'subject_id' => $subIoT->id, 'teacher_id' => $bagas->id, 'room' => 'Lab IoT & Embedded'],
            ];

            foreach ($schedules as $sc) {
                Schedule::create(array_merge($sc, ['class_id' => $kelasIndustri->id]));
            }

            // 8. SESI PRESENSI HARIAN RIIL (Hari Ini)
            $today = Carbon::today()->format('Y-m-d');
            $session = AttendanceSession::create([
                'class_id' => $kelasIndustri->id,
                'date' => $today,
                'status' => 'DRAFT',
                'verification_status' => 'TERVERIFIKASI',
                'school_hours' => '07:30 - 15:00',
                'room' => 'Lab RPL Industri',
            ]);

            // 13 Hadir, 1 Sakit (Vidhie Musyafa), 1 Izin (Nadila)
            foreach ($createdStudents as $st) {
                $status = 'HADIR';
                $notes = 'Hadir tepat waktu mengikuti KBM Industri';

                if ($st->name === 'Vidhie Musyafa') {
                    $status = 'SAKIT';
                    $notes = 'Surat keterangan dokter klinik terlampir';
                } elseif ($st->name === 'Nadila') {
                    $status = 'IZIN';
                    $notes = 'Dispensasi delegasi lomba kompetensi';
                }

                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $st->id,
                    'status' => $status,
                    'note' => $notes,
                ]);
            }

            // 9. JURNAL KELAS HARIAN (Hari Ini)
            ClassJournal::create([
                'class_id' => $kelasIndustri->id,
                'schedule_id' => Schedule::first()->id,
                'date' => $today,
                'activity' => 'Pengembangan Arsitektur Full-Stack REST API & State Management Nuxt 3',
                'notes' => 'Siswa mempraktikkan integrasi frontend-backend secara langsung di lab industri.',
                'verification_status' => 'TERVERIFIKASI',
                'confirmed_at' => Carbon::now(),
                'confirmed_by_id' => $bagas->id,
                'created_by_id' => $kelasIndustri->secretary_id,
            ]);

            // 10. PERMOHONAN SURAT IZIN SISWA KELAS INDUSTRI
            $vidhie = User::where('email', 'vidhie@sekolah.sch.id')->first();
            $nadila = User::where('email', 'nadila@sekolah.sch.id')->first();

            LeaveRequest::create([
                'student_id' => $vidhie->id,
                'submitted_by_id' => $vidhie->id,
                'type' => 'SAKIT',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDays(2),
                'recipient' => 'Wali Kelas Industri / Bu Fadhilah',
                'reason' => 'Demam dan flu berat membutuhkan istirahat rawat jalan sesuai petunjuk dokter.',
                'status' => 'PENDING',
            ]);

            LeaveRequest::create([
                'student_id' => $nadila->id,
                'submitted_by_id' => $nadila->id,
                'type' => 'IZIN',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDay(),
                'recipient' => 'Wali Kelas Industri / Bu Fadhilah',
                'reason' => 'Mengikuti pembinaan intensif persiapan LKS Web Technologies mewakili sekolah.',
                'status' => 'PENDING',
            ]);

            DB::commit();
            echo "SUCCESS: Database bersih khusus Kelas Industri berhasil diinisialisasi!\n";
            echo "- Rombel: 1 Kelas (Kelas Industri)\n";
            echo "- Wali Kelas: Bu Fadhilah (Nurhayatul Fadilla, S.Pd)\n";
            echo "- Guru Pengajar: Pak Bagas (Bagas Pratama, S.Kom)\n";
            echo "- Total Siswa: 15 Siswa (7 dari XI RPL 1, 8 dari XI RPL 2)\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
