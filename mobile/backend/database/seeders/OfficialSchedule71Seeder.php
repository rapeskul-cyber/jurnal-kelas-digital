<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Subject;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OfficialSchedule71Seeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. DAFTAR 26 GURU RESMI SMKN 71 JAKARTA (Per 20 Juli 2026)
            $teacherData = [
                ['code' => 'UH', 'name' => 'Umi Haniah, S.Pd', 'specialization' => 'Bahasa Indonesia'],
                ['code' => 'MX', 'name' => 'Dra. Mexi Noviyanti', 'specialization' => 'Matematika'],
                ['code' => 'CH', 'name' => 'Chomisari Handayani, S.Pd', 'specialization' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)'],
                ['code' => 'JV', 'name' => 'Jenny Vera Nursjam, M.Pd', 'specialization' => 'Bahasa Inggris'],
                ['code' => 'NA', 'name' => 'Nanda Arsyia Murti, S.Kom', 'specialization' => 'Dasar Program Keahlian RPL & Informatika'],
                ['code' => 'MU', 'name' => 'Muhammad Usman, S.Si', 'specialization' => 'Pendidikan Agama Islam (PAI) & Pancasila'],
                ['code' => 'SW', 'name' => 'Samuel Wicaksana, M.Pd', 'specialization' => 'Pendidikan Agama Kristen (PAK) & PP'],
                ['code' => 'DY', 'name' => 'Dwi Yuniastuti, S.Pd', 'specialization' => 'Sejarah & Pendidikan Pancasila'],
                ['code' => 'DE', 'name' => 'Dwiadi Eliyanto, S.Pd', 'specialization' => 'Bahasa Inggris & Bahasa Indonesia'],
                ['code' => 'NK', 'name' => 'Nurina Kartika Ayu, S.Pd', 'specialization' => 'Pendidikan Jasmani Olahraga & Kesehatan (PJOK)'],
                ['code' => 'FI', 'name' => 'Fanny Indriyani, S.Pd', 'specialization' => 'Bimbingan Konseling (BP/BK)'],
                ['code' => 'KA', 'name' => 'Khairul Arifin, S.Pd', 'specialization' => 'Dasar Desain Grafis DKV & Mata Pelajaran Pilihan'],
                ['code' => 'MD', 'name' => 'Maidya Nengsih, S.Pd', 'specialization' => 'Matematika'],
                ['code' => 'DH', 'name' => 'Dalilah, S.Pd', 'specialization' => 'Bahasa Indonesia & Bahasa Inggris'],
                ['code' => 'AZ', 'name' => 'Azizah Khoiro Nisah, S.Pd', 'specialization' => 'Konsentrasi Keahlian RPL & DKV'],
                ['code' => 'FA', 'name' => 'Faradillah Aryani, S.T', 'specialization' => 'Kreativitas & Inovasi Kejuruan (KIK/PKK)'],
                ['code' => 'AL', 'name' => 'Anggi Laras Pratiwi, S.Sn', 'specialization' => 'Seni Budaya & Desain Grafis'],
                ['code' => 'MZ', 'name' => 'Miftahul Zannah, S.Pd', 'specialization' => 'Konsentrasi Keahlian Animasi & RPL'],
                ['code' => 'NY', 'name' => 'Nurhadi Yahya, S.Ds', 'specialization' => 'Konsentrasi Keahlian Desain Komunikasi Visual (DKV)'],
                ['code' => 'AO', 'name' => 'Anisha Oktaviana, S.Pd', 'specialization' => 'Informatika & Animasi'],
                ['code' => 'FK', 'name' => 'Fonny Krisnawulan PM, S.Ds', 'specialization' => 'Konsentrasi Keahlian Animasi 2D/3D'],
                ['code' => 'RS', 'name' => 'Rahmat Setiawan, S.Ds', 'specialization' => 'Konsentrasi Keahlian DKV & Animasi'],
                ['code' => 'NF', 'name' => 'Nurhayatul Fadilla, S.Pd', 'specialization' => 'Konsentrasi Keahlian RPL & DPK'],
                ['code' => 'QA', 'name' => 'Qisthina Arsi Febriani, S.Psi', 'specialization' => 'Bimbingan Konseling & Psikologi Pendidikan'],
                ['code' => 'AR', 'name' => 'Arif Rahmadi, S.Pd.I', 'specialization' => 'Pendidikan Agama Islam (PAI)'],
                ['code' => 'VT', 'name' => 'Veranika TM, S.Kom', 'specialization' => 'Konsentrasi Keahlian Rekayasa Perangkat Lunak (RPL)'],
            ];

            $teacherMap = []; // code => User model
            foreach ($teacherData as $idx => $t) {
                $slug = strtolower($t['code']);
                $email = "guru.{$slug}@sekolah.sch.id";
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $t['name'],
                        'password' => Hash::make('password123'),
                        'phone' => '0812' . str_pad($idx + 10, 8, '0', STR_PAD_LEFT),
                        'nip_nis' => '1985' . str_pad($idx + 1, 10, '0', STR_PAD_LEFT),
                        'role' => 'GURU_PENGAJAR',
                        'specialization' => $t['specialization'],
                        'is_active' => true,
                    ]
                );
                
                // Update name and specialization to match exactly
                $user->name = $t['name'];
                $user->specialization = $t['specialization'];
                $user->save();

                $teacherMap[$t['code']] = $user;
            }

            // Map existing quick login teacher Budi Santoso -> NA (Nanda) or default
            $budi = User::where('email', 'budi.santoso@gmail.com')->first();
            if ($budi) {
                $teacherMap['BUDI'] = $budi;
            }

            // 2. DAFTAR MATA PELAJARAN RESMI
            $subjectDefs = [
                // Mata Pelajaran Umum
                ['code' => 'MTK', 'name' => 'Matematika', 'major' => 'UMUM'],
                ['code' => 'PP', 'name' => 'Pendidikan Pancasila', 'major' => 'UMUM'],
                ['code' => 'SEJ', 'name' => 'Sejarah Indonesia', 'major' => 'UMUM'],
                ['code' => 'B.ING', 'name' => 'Bahasa Inggris', 'major' => 'UMUM'],
                ['code' => 'B.INDO', 'name' => 'Bahasa Indonesia', 'major' => 'UMUM'],
                ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'major' => 'UMUM'],
                ['code' => 'PAK', 'name' => 'Pendidikan Agama Kristen', 'major' => 'UMUM'],
                ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani Olahraga & Kesehatan', 'major' => 'UMUM'],
                ['code' => 'IPAS', 'name' => 'Ilmu Pengetahuan Alam & Sosial', 'major' => 'UMUM'],
                ['code' => 'INFKKA', 'name' => 'Informatika', 'major' => 'UMUM'],
                ['code' => 'SB', 'name' => 'Seni Budaya', 'major' => 'UMUM'],
                ['code' => 'ML', 'name' => 'Muatan Lokal', 'major' => 'UMUM'],

                // Kejuruan RPL
                ['code' => 'DPK-RPL', 'name' => 'Dasar Program Keahlian RPL', 'major' => 'RPL'],
                ['code' => 'MKK-RPL', 'name' => 'Mata Pelajaran Konsentrasi Keahlian RPL', 'major' => 'RPL'],
                ['code' => 'KIK-RPL', 'name' => 'Kreativitas & Inovasi Kejuruan (PKK) RPL', 'major' => 'RPL'],
                ['code' => 'MPP-RPL', 'name' => 'Mata Pelajaran Pilihan RPL', 'major' => 'RPL'],

                // Kejuruan DKV
                ['code' => 'DPK-DKV', 'name' => 'Dasar Program Keahlian DKV', 'major' => 'DKV'],
                ['code' => 'MKK-DKV', 'name' => 'Mata Pelajaran Konsentrasi Keahlian DKV', 'major' => 'DKV'],
                ['code' => 'KIK-DKV', 'name' => 'Kreativitas & Inovasi Kejuruan (PKK) DKV', 'major' => 'DKV'],
                ['code' => 'MPP-DKV', 'name' => 'Mata Pelajaran Pilihan DKV', 'major' => 'DKV'],

                // Kejuruan Animasi
                ['code' => 'DPK-ANI', 'name' => 'Dasar Program Keahlian Animasi', 'major' => 'Animasi'],
                ['code' => 'MKK-ANI', 'name' => 'Mata Pelajaran Konsentrasi Keahlian Animasi', 'major' => 'Animasi'],
                ['code' => 'KIK-ANI', 'name' => 'Kreativitas & Inovasi Kejuruan (PKK) Animasi', 'major' => 'Animasi'],
                ['code' => 'MPP-ANI', 'name' => 'Mata Pelajaran Pilihan Animasi', 'major' => 'Animasi'],
            ];

            $subjectMap = []; // code_major => Subject
            foreach ($subjectDefs as $s) {
                $sub = Subject::firstOrCreate(
                    ['code' => $s['code']],
                    [
                        'name' => $s['name'],
                        'major' => $s['major'],
                    ]
                );
                $sub->name = $s['name'];
                $sub->major = $s['major'];
                $sub->save();

                $subjectMap[$s['code']] = $sub;
            }

            // 3. AMBIL DATA JADWAL PARSED DARI JSON
            $jsonPath = '/home/agentuser/jurnal-kelas-digital/desktop/backend/database/data/parsed_schedules.json';
            if (!file_exists($jsonPath)) {
                throw new \Exception("File {$jsonPath} tidak ditemukan.");
            }

            $schedulesData = json_decode(file_get_contents($jsonPath), true);
            if (!is_array($schedulesData)) {
                throw new \Exception("Data parsed_schedules.json tidak valid.");
            }

            // Bersihkan jadwal lama dan ganti dengan jadwal resmi SMKN 71
            Schedule::truncate();

            // Load All Classes
            $classes = SchoolClass::all()->keyBy('name');

            $insertedCount = 0;
            foreach ($schedulesData as $item) {
                $className = $item['class'];
                // Handle X ANM -> X Animasi 1
                if ($className === 'X ANM') {
                    $className = 'X Animasi 1';
                }

                $cls = $classes->get($className);
                if (!$cls) {
                    continue;
                }

                // Resolve Subject
                $rawSub = $item['subject_code'];
                $major = $item['major'];
                $subjectKey = $rawSub;
                if (in_array($rawSub, ['DPK', 'MKK', 'KIK', 'MPP'])) {
                    $prefix = $major === 'Animasi' ? 'ANI' : $major;
                    $subjectKey = "{$rawSub}-{$prefix}";
                }
                $subject = $subjectMap[$subjectKey] ?? $subjectMap['MKK-RPL'];

                // Resolve Teacher
                $teacherCode = $item['teacher_code'];
                $teacher = $teacherMap[$teacherCode] ?? $teacherMap['NA'] ?? $teacherMap['BUDI'] ?? null;
                $teacherId = $teacher ? $teacher->id : null;

                Schedule::create([
                    'class_id' => $cls->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacherId,
                    'day' => $item['day'],
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'room' => $item['room'],
                ]);
                $insertedCount++;

                // Attach taughtClasses relation to teacher
                if ($teacher) {
                    $teacher->taughtClasses()->syncWithoutDetaching([$cls->id]);
                }
            }

            DB::commit();
            echo "SUCCESS: {$insertedCount} jadwal resmi SMKN 71 Jakarta berhasil diimpor beserta 26 guru dan mata pelajaran!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
